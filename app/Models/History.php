<?php
// app/Models/History.php - UPDATED

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class History extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'user_id',
        'dropbox_id',
        'waste_type',
        'weight',
        'coins_earned',
        'status',
        'scan_time',
        'error_message',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'weight' => 'decimal:3',
        'coins_earned' => 'integer',
        'scan_time' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relationship with User
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relationship with Dropbox
     */
    public function dropbox()
    {
        return $this->belongsTo(Dropbox::class);
    }

    /**
     * Scope for successful scans
     */
    public function scopeSuccessful($query)
    {
        return $query->where('status', 'success');
    }

    /**
     * Scope for failed scans
     */
    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    /**
     * Scope for today's scans
     */
    public function scopeToday($query)
    {
        return $query->whereDate('created_at', Carbon::today());
    }

    /**
     * Scope for this week's scans
     */
    public function scopeThisWeek($query)
    {
        return $query->whereBetween('created_at', [
            Carbon::now()->startOfWeek(),
            Carbon::now()->endOfWeek()
        ]);
    }

    /**
     * Accessor for formatted weight
     */
    public function getFormattedWeightAttribute()
    {
        return $this->weight ? number_format($this->weight, 2) . ' kg' : '0 kg';
    }

    /**
     * Accessor for time ago
     */
    public function getTimeAgoAttribute()
    {
        return $this->created_at->diffForHumans();
    }

    /**
     * Get status badge color
     */
    public function getStatusColorAttribute()
    {
        return $this->status === 'success' ? '#28a745' : '#dc3545';
    }
}

// ================================================================
// app/Models/Transaction.php - UPDATED

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
              ->orWhere('amount_coins', '>', 0);
        });
    }

    /**
     * Scope untuk expense transactions
     */
    public function scopeExpense($query)
    {
        return $query->where(function($q) {
            $q->where('amount_rp', '<', 0)
              ->orWhere('amount_coins', '<', 0);
        });
    }

    /**
     * Accessor untuk format rupiah
     */
    public function getFormattedAmountRpAttribute()
    {
        if (!$this->amount_rp) return null;

        $prefix = $this->amount_rp >= 0 ? '+' : '';
        return $prefix . 'Rp ' . number_format(abs($this->amount_rp), 0, ',', '.');
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
     * Get transaction type label
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
        ];

        return $labels[$this->type] ?? ucfirst(str_replace('_', ' ', $this->type));
    }

    /**
     * Check if transaction is income
     */
    public function getIsIncomeAttribute()
    {
        return in_array($this->type, ['topup', 'manual_topup', 'scan_reward', 'transfer_in']) ||
               ($this->amount_rp && $this->amount_rp > 0) ||
               ($this->amount_coins && $this->amount_coins > 0);
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
