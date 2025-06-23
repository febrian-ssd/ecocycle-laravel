<?php
// app/Models/User.php - CLEAN SINGLE FILE

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
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
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
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
     * Get formatted saldo - FIXED TYPE ISSUE
     */
    public function getFormattedSaldoAttribute()
    {
        $balance = $this->balance_rp ? (float) $this->balance_rp : 0;
        return 'Rp ' . number_format($balance, 0, ',', '.');
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
