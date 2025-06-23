<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens; // <-- ADD THIS IMPORT

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens; // <-- ADD HasApiTokens HERE

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'is_admin',
        'balance_rp',
        'balance_coins',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_admin' => 'boolean',
            'balance_rp' => 'decimal:2',
            'balance_coins' => 'integer',
        ];
    }

    /**
     * Relationship dengan TopupRequest yang dibuat user
     */
    public function topupRequests()
    {
        return $this->hasMany(TopupRequest::class);
    }

    /**
     * Relationship dengan TopupRequest yang disetujui oleh admin
     */
    public function approvedTopups()
    {
        return $this->hasMany(TopupRequest::class, 'approved_by');
    }

    /**
     * Relationship dengan TopupRequest yang ditolak oleh admin
     */
    public function rejectedTopups()
    {
        return $this->hasMany(TopupRequest::class, 'rejected_by');
    }

    /**
     * Check if user is admin
     */
    public function isAdmin()
    {
        return $this->is_admin;
    }

    /**
     * Get formatted saldo
     */
    public function getFormattedSaldoAttribute()
    {
        return 'Rp ' . number_format($this->balance_rp, 0, ',', '.');
    }

    /**
     * Get saldo attribute (alias for balance_rp)
     */
    public function getSaldoAttribute()
    {
        return $this->balance_rp;
    }

    /**
     * Add saldo to user
     */
    public function addSaldo($amount)
    {
        $this->increment('balance_rp', $amount);
        return $this;
    }

    /**
     * Subtract saldo from user
     */
    public function subtractSaldo($amount)
    {
        if ($this->balance_rp >= $amount) {
            $this->decrement('balance_rp', $amount);
            return $this;
        }

        throw new \Exception('Saldo tidak mencukupi');
    }

    /**
     * Check if user has sufficient saldo
     */
    public function hasSufficientSaldo($amount)
    {
        return $this->balance_rp >= $amount;
    }

    /**
     * Scope untuk filter admin users
     */
    public function scopeAdmins($query)
    {
        return $query->where('is_admin', true);
    }

    /**
     * Scope untuk filter regular users
     */
    public function scopeRegularUsers($query)
    {
        return $query->where('is_admin', false);
    }
}
