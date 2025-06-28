<?php
// app/Models/Transaction.php - DIPERBAIKI: Support semua transaction types
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type',
        'amount_rp',
        'amount_coins',
        'description',
    ];

    protected $casts = [
        'amount_rp' => 'decimal:2',
        'amount_coins' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // PERBAIKAN: Daftar lengkap transaction types yang didukung
    public static $validTypes = [
        'topup',
        'manual_topup',
        'coin_exchange_to_rp',
        'scan_reward',
        'transfer_out',
        'transfer_in',  // Tambahan untuk transfer masuk
        'exchange_coins',
    ];

    /**
     * Relationship dengan User
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope untuk transaksi hari ini
     */
    public function scopeToday($query)
    {
        return $query->whereDate('created_at', today());
    }

    /**
     * Scope untuk transaksi bulan ini
     */
    public function scopeThisMonth($query)
    {
        return $query->whereMonth('created_at', now()->month)
                     ->whereYear('created_at', now()->year);
    }

    /**
     * Scope untuk income transactions
     */
    public function scopeIncome($query)
    {
        return $query->where(function($q) {
            $q->where('amount_rp', '>', 0)
              ->orWhere('amount_coins', '>', 0)
              ->orWhereIn('type', ['topup', 'manual_topup', 'scan_reward', 'transfer_in']);
        });
    }

    /**
     * Scope untuk expense transactions
     */
    public function scopeExpense($query)
    {
        return $query->where(function($q) {
            $q->where('amount_rp', '<', 0)
              ->orWhere('amount_coins', '<', 0)
              ->orWhereIn('type', ['transfer_out', 'exchange_coins']);
        });
    }

    /**
     * Accessor untuk format rupiah
     */
    public function getFormattedAmountRpAttribute()
    {
        if (!$this->amount_rp) return null;

        $amount = (float) $this->amount_rp;
        $prefix = $amount >= 0 ? '+' : '';
        return $prefix . 'Rp ' . number_format(abs($amount), 0, ',', '.');
    }

    /**
     * Accessor untuk format koin
     */
    public function getFormattedAmountCoinsAttribute()
    {
        if (!$this->amount_coins) return null;

        $prefix = $this->amount_coins >= 0 ? '+' : '';
        return $prefix . $this->amount_coins . ' koin';
    }

    /**
     * Get transaction type label - DIPERBAIKI: Support semua types
     */
    public function getTypeLabelAttribute()
    {
        $labels = [
            'topup' => 'Top Up',
            'manual_topup' => 'Top Up Manual',
            'coin_exchange_to_rp' => 'Tukar Koin',
            'scan_reward' => 'Reward Scan',
            'transfer_out' => 'Transfer Keluar',
            'transfer_in' => 'Transfer Masuk',
            'exchange_coins' => 'Tukar Koin',
        ];

        return $labels[$this->type] ?? ucfirst(str_replace('_', ' ', $this->type));
    }

    /**
     * Check if transaction is income - DIPERBAIKI: Logic yang lebih akurat
     */
    public function getIsIncomeAttribute()
    {
        // Income transaction types
        $incomeTypes = ['topup', 'manual_topup', 'scan_reward', 'transfer_in'];

        if (in_array($this->type, $incomeTypes)) {
            return true;
        }

        // Fallback: check amount
        if (($this->amount_rp && $this->amount_rp > 0) ||
            ($this->amount_coins && $this->amount_coins > 0)) {
            return true;
        }

        return false;
    }

    /**
     * Get transaction icon
     */
    public function getIconAttribute()
    {
        $icons = [
            'topup' => 'fas fa-plus-circle',
            'manual_topup' => 'fas fa-hand-holding-usd',
            'coin_exchange_to_rp' => 'fas fa-exchange-alt',
            'scan_reward' => 'fas fa-qrcode',
            'transfer_out' => 'fas fa-arrow-up',
            'transfer_in' => 'fas fa-arrow-down',
            'exchange_coins' => 'fas fa-exchange-alt',
        ];

        return $icons[$this->type] ?? 'fas fa-circle';
    }

    /**
     * Get transaction color
     */
    public function getColorAttribute()
    {
        if ($this->is_income) {
            return '#28a745'; // green
        } else {
            return '#dc3545'; // red
        }
    }
}
