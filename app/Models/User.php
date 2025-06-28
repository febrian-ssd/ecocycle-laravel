<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens;

    const ROLE_ADMIN = 'admin';
    const ROLE_USER = 'user';

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'balance_rp',
        'balance_coins',
        'is_active',
        'phone_number',
        'address',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'balance_rp' => 'decimal:2',
            'balance_coins' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    // Role-based methods
    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    public function isUser(): bool
    {
        return $this->role === self::ROLE_USER;
    }

    public function hasRole(string $role): bool
    {
        return $this->role === $role;
    }

    public function getRoleDisplayName(): string
    {
        return match($this->role) {
            self::ROLE_ADMIN => 'Administrator',
            self::ROLE_USER => 'User',
            default => 'Unknown'
        };
    }

    // Scope for filtering by role
    public function scopeAdmins($query)
    {
        return $query->where('role', self::ROLE_ADMIN);
    }

    public function scopeUsers($query)
    {
        return $query->where('role', self::ROLE_USER);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // Relationships
    public function topupRequests()
    {
        return $this->hasMany(TopupRequest::class);
    }

    public function histories()
    {
        return $this->hasMany(History::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    // Wallet management
    public function getFormattedBalanceRpAttribute(): string
    {
        return 'Rp ' . number_format($this->balance_rp ?? 0, 0, ',', '.');
    }

    public function addBalance(float $amount): bool
    {
        $this->increment('balance_rp', $amount);
        return true;
    }

    public function subtractBalance(float $amount): bool
    {
        if ($this->balance_rp >= $amount) {
            $this->decrement('balance_rp', $amount);
            return true;
        }
        return false;
    }

    public function addCoins(int $coins): bool
    {
        $this->increment('balance_coins', $coins);
        return true;
    }

    public function subtractCoins(int $coins): bool
    {
        if ($this->balance_coins >= $coins) {
            $this->decrement('balance_coins', $coins);
            return true;
        }
        return false;
    }

    public function getInitialsAttribute(): string
    {
        $words = explode(' ', $this->name);
        if (count($words) >= 2) {
            return strtoupper($words[0][0] . $words[1][0]);
        } else if (count($words) > 0) {
            return strtoupper($words[0][0]);
        }
        return 'U';
    }
}
