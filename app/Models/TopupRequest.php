<?php
// app/Models/TopupRequest.php - FINAL TYPE SAFE VERSION

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class TopupRequest extends Model
{
    use HasFactory;

    protected $table = 'topup_requests';

    protected $fillable = [
        'user_id',
        'amount',
        'status',
        'type',
        'payment_method',
        'payment_proof',
        'approved_by',
        'rejected_by',
        'approved_at',
        'rejected_at',
        'admin_note',
        'user_note'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relationship dengan User yang mengajukan top up
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Relationship dengan Admin yang menyetujui
     */
    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Relationship dengan Admin yang menolak
     */
    public function rejectedBy()
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }

    /**
     * Scope untuk permintaan yang pending
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope untuk permintaan yang disetujui
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Scope untuk permintaan yang ditolak
     */
    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    /**
     * Scope untuk permintaan hari ini
     */
    public function scopeToday($query)
    {
        return $query->whereDate('created_at', Carbon::today());
    }

    /**
     * Scope untuk permintaan minggu ini
     */
    public function scopeThisWeek($query)
    {
        return $query->whereBetween('created_at', [
            Carbon::now()->startOfWeek(),
            Carbon::now()->endOfWeek()
        ]);
    }

    /**
     * Accessor untuk format rupiah - FIXED TYPE ISSUE
     */
    public function getFormattedAmountAttribute()
    {
        $amount = $this->amount ? (float) $this->amount : 0;
        return 'Rp ' . number_format($amount, 0, ',', '.');
    }

    /**
     * Accessor untuk status badge
     */
    public function getStatusBadgeAttribute()
    {
        $badges = [
            'pending' => '<span class="badge bg-warning">Menunggu</span>',
            'approved' => '<span class="badge bg-success">Disetujui</span>',
            'rejected' => '<span class="badge bg-danger">Ditolak</span>',
        ];

        return $badges[$this->status] ?? '<span class="badge bg-secondary">Unknown</span>';
    }

    /**
     * Mutator untuk amount (pastikan dalam format yang benar)
     */
    public function setAmountAttribute($value)
    {
        // Clean up the value and convert to float
        $cleanValue = str_replace(['Rp', '.', ',', ' '], '', $value);
        $this->attributes['amount'] = (float) $cleanValue;
    }

    /**
     * Check if request can be processed
     */
    public function canBeProcessed()
    {
        return $this->status === 'pending';
    }

    /**
     * Get processing admin name
     */
    public function getProcessedByAttribute()
    {
        if ($this->status === 'approved' && $this->approvedBy) {
            return $this->approvedBy->name;
        } elseif ($this->status === 'rejected' && $this->rejectedBy) {
            return $this->rejectedBy->name;
        }
        return null;
    }

    /**
     * Get safe amount as float
     */
    public function getAmountAsFloat()
    {
        return $this->amount ? (float) $this->amount : 0.0;
    }

    /**
     * Format amount for display
     */
    public function formatAmount($amount = null)
    {
        $value = $amount ?? $this->amount;
        $floatValue = $value ? (float) $value : 0;
        return 'Rp ' . number_format($floatValue, 0, ',', '.');
    }
}
