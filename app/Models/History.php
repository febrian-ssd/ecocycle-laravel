<?php
// app/Models/History.php - FINAL CARBON SAFE VERSION

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
     * Scope for today's scans - CARBON SAFE
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
     * Accessor for formatted weight - FIXED TYPE ISSUE
     */
    public function getFormattedWeightAttribute()
    {
        $weight = $this->weight ? (float) $this->weight : 0;
        return number_format($weight, 2) . ' kg';
    }

    /**
     * Accessor for time ago - CARBON SAFE VERSION
     */
    public function getTimeAgoAttribute()
    {
        try {
            if ($this->created_at && $this->created_at instanceof \Carbon\Carbon) {
                return $this->created_at->diffForHumans();
            }
            return 'Unknown';
        } catch (\Exception $e) {
            return 'Unknown';
        }
    }

    /**
     * Get status badge color
     */
    public function getStatusColorAttribute()
    {
        return $this->status === 'success' ? '#28a745' : '#dc3545';
    }

    /**
     * Check if scan was today - CARBON SAFE VERSION
     */
    public function isTodayScan()
    {
        try {
            if ($this->created_at && $this->created_at instanceof \Carbon\Carbon) {
                return $this->created_at->isToday();
            }
            return false;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Safe method to check if created today
     */
    public function isCreatedToday()
    {
        try {
            if (!$this->created_at) {
                return false;
            }

            // Convert to Carbon if it's not already
            $date = $this->created_at instanceof \Carbon\Carbon
                ? $this->created_at
                : Carbon::parse($this->created_at);

            return $date->isToday();
        } catch (\Exception $e) {
            return false;
        }
    }
}
