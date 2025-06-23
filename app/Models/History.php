<?php
// app/Models/History.php - FIXED TYPE ISSUES

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
     * Accessor for formatted weight - FIXED TYPE ISSUE
     */
    public function getFormattedWeightAttribute()
    {
        $weight = $this->weight ? (float) $this->weight : 0;
        return number_format($weight, 2) . ' kg';
    }

    /**
     * Accessor for time ago - FIXED CARBON ISSUE
     */
    public function getTimeAgoAttribute()
    {
        return $this->created_at ? $this->created_at->diffForHumans() : 'Unknown';
    }

    /**
     * Get status badge color
     */
    public function getStatusColorAttribute()
    {
        return $this->status === 'success' ? '#28a745' : '#dc3545';
    }

    /**
     * Check if scan was today - FIXED METHOD
     */
    public function isTodayScan()
    {
        return $this->created_at ? $this->created_at->isToday() : false;
    }
}

// ================================================================
// app/Models/Transaction.php - FIXED TYPE ISSUES

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
     * Accessor untuk format rupiah - FIXED TYPE ISSUE
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

// ================================================================
// app/Models/TopupRequest.php - FIXED TYPE ISSUES

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
        $this->attributes['amount'] = (float) str_replace(['Rp', '.', ',', ' '], '', $value);
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
}

// ================================================================
// app/Models/User.php - FIXED TYPE ISSUES

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

// ================================================================
// app/Http/Controllers/Admin/HistoryController.php - FIXED CARBON ISSUE

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\History;
use Carbon\Carbon;

class HistoryController extends Controller
{
    /**
     * Menampilkan halaman riwayat scan dengan statistik.
     */
    public function index()
    {
        $histories = History::with(['user', 'dropbox'])
                           ->latest()
                           ->get();

        // Hitung statistik untuk kartu dashboard
        $totalScans = $histories->count();
        $successScans = $histories->where('status', 'success')->count();
        $failedScans = $histories->where('status', 'failed')->count();

        // Hitung scan hari ini - FIXED CARBON ISSUE
        $todayScans = $histories->filter(function ($history) {
            return $history->created_at && $history->created_at->isToday();
        })->count();

        return view('admin.history.index', compact(
            'histories',
            'totalScans',
            'successScans',
            'failedScans',
            'todayScans'
        ));
    }

    /**
     * Menampilkan detail riwayat scan tertentu.
     */
    public function show(History $history)
    {
        $history->load(['user', 'dropbox']);
        return view('admin.history.show', compact('history'));
    }

    /**
     * Menghapus riwayat scan (opsional).
     */
    public function destroy(History $history)
    {
        try {
            $history->delete();

            return redirect()->route('admin.history.index')
                           ->with('success', 'Riwayat scan berhasil dihapus!');

        } catch (\Exception $e) {
            return redirect()->route('admin.history.index')
                           ->with('error', 'Gagal menghapus riwayat: ' . $e->getMessage());
        }
    }

    /**
     * API endpoint untuk mendapatkan statistik real-time.
     */
    public function getStats()
    {
        $histories = History::all();

        // FIXED: Use Carbon properly for today check
        $todayCount = $histories->filter(function ($history) {
            return $history->created_at && $history->created_at->isToday();
        })->count();

        return response()->json([
            'total' => $histories->count(),
            'success' => $histories->where('status', 'success')->count(),
            'failed' => $histories->where('status', 'failed')->count(),
            'today' => $todayCount
        ]);
    }

    /**
     * Export data riwayat ke CSV.
     */
    public function export(Request $request)
    {
        $query = History::with(['user', 'dropbox'])->latest();

        // Filter berdasarkan parameter yang diberikan
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        if ($request->has('date') && $request->date) {
            $query->whereDate('created_at', $request->date);
        }

        $histories = $query->get();

        $filename = 'riwayat-scan-' . date('Y-m-d') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($histories) {
            $file = fopen('php://output', 'w');

            // CSV Headers
            fputcsv($file, [
                'ID Riwayat',
                'Nama User',
                'Email User',
                'Lokasi Dropbox',
                'Status Scan',
                'Waktu Scan',
                'Tanggal'
            ]);

            // CSV Data
            foreach ($histories as $history) {
                fputcsv($file, [
                    $history->id,
                    $history->user ? $history->user->name : 'User Telah Dihapus',
                    $history->user ? $history->user->email : 'N/A',
                    $history->dropbox ? $history->dropbox->location_name : 'Dropbox Telah Dihapus',
                    ucfirst($history->status),
                    $history->created_at ? $history->created_at->format('H:i:s') : '',
                    $history->created_at ? $history->created_at->format('d/m/Y') : ''
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Mendapatkan data untuk chart/grafik (opsional).
     */
    public function getChartData()
    {
        // Data untuk chart mingguan
        $weeklyData = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $dayScans = History::whereDate('created_at', $date)->count();
            $weeklyData[] = [
                'date' => $date->format('d/m'),
                'scans' => $dayScans
            ];
        }

        // Data untuk chart status
        $statusData = [
            'success' => History::where('status', 'success')->count(),
            'failed' => History::where('status', 'failed')->count()
        ];

        return response()->json([
            'weekly' => $weeklyData,
            'status' => $statusData
        ]);
    }
}

// ================================================================
// app/Http/Controllers/Api/HistoryController.php - FIXED TYPE ISSUE

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\History;
use App\Models\Transaction;

class HistoryController extends Controller
{
    /**
     * Get user scan history
     */
    public function getScanHistory(Request $request)
    {
        $user = $request->user();

        $history = History::where('user_id', $user->id)
                         ->with('dropbox')
                         ->orderBy('created_at', 'desc')
                         ->limit(50)
                         ->get();

        return response()->json([
            'success' => true,
            'data' => $history->map(function($item) {
                return [
                    'id' => $item->id,
                    'waste_type' => $item->waste_type ?? 'plastic',
                    'weight' => $item->weight ?? 0,
                    'coins_earned' => $item->coins_earned ?? 0,
                    'status' => $item->status,
                    'scan_time' => $item->scan_time ?? $item->created_at,
                    'created_at' => $item->created_at,
                    'dropbox' => $item->dropbox ? [
                        'id' => $item->dropbox->id,
                        'location_name' => $item->dropbox->location_name,
                        'latitude' => $item->dropbox->latitude,
                        'longitude' => $item->dropbox->longitude,
                    ] : null,
                ];
            })
        ]);
    }

    /**
     * Get user scan statistics
     */
    public function getScanStats(Request $request)
    {
        $user = $request->user();

        $totalScans = History::where('user_id', $user->id)->count();
        $successfulScans = History::where('user_id', $user->id)->where('status', 'success')->count();
        $totalCoinsEarned = History::where('user_id', $user->id)->sum('coins_earned');
        $totalWasteWeight = History::where('user_id', $user->id)->sum('weight');

        // Convert decimal to float for proper calculation - FIXED TYPE ISSUE
        $totalWasteWeight = $totalWasteWeight ? (float) $totalWasteWeight : 0.0;

        // Statistik per jenis sampah
        $wasteTypeStats = History::where('user_id', $user->id)
            ->where('status', 'success')
            ->selectRaw('waste_type, COUNT(*) as count, SUM(weight) as total_weight, SUM(coins_earned) as total_coins')
            ->groupBy('waste_type')
            ->get();

        // Scan minggu ini
        $weeklyScans = History::where('user_id', $user->id)
            ->where('created_at', '>=', now()->startOfWeek())
            ->where('status', 'success')
            ->count();

        // Scan bulan ini
        $monthlyScans = History::where('user_id', $user->id)
            ->where('created_at', '>=', now()->startOfMonth())
            ->where('status', 'success')
            ->count();

        return response()->json([
            'success' => true,
            'data' => [
                'total_scans' => $totalScans,
                'successful_scans' => $successfulScans,
                'total_coins_earned' => $totalCoinsEarned,
                'total_waste_weight' => round($totalWasteWeight, 2),
                'success_rate' => $totalScans > 0 ? round(($successfulScans / $totalScans) * 100, 1) : 0,
                'weekly_scans' => $weeklyScans,
                'monthly_scans' => $monthlyScans,
                'waste_type_breakdown' => $wasteTypeStats,
            ]
        ]);
    }

    /**
     * Get user transaction history with better formatting
     */
    public function getTransactionHistory(Request $request)
    {
        $user = $request->user();

        $transactions = Transaction::where('user_id', $user->id)
                                 ->orderBy('created_at', 'desc')
                                 ->limit(50)
                                 ->get();

        return response()->json([
            'success' => true,
            'data' => $transactions->map(function($transaction) {
                return [
                    'id' => $transaction->id,
                    'type' => $transaction->type,
                    'amount_rp' => $transaction->amount_rp,
                    'amount_coins' => $transaction->amount_coins,
                    'description' => $transaction->description,
                    'created_at' => $transaction->created_at,
                    'formatted_amount_rp' => $transaction->amount_rp ?
                        ($transaction->amount_rp >= 0 ? '+' : '') . 'Rp ' . number_format((float) $transaction->amount_rp, 0, ',', '.') :
                        null,
                    'formatted_amount_coins' => $transaction->amount_coins ?
                        ($transaction->amount_coins >= 0 ? '+' : '') . $transaction->amount_coins . ' koin' :
                        null,
                    'type_label' => $this->getTypeLabel($transaction->type),
                    'is_income' => $this->isIncomeTransaction($transaction),
                ];
            })
        ]);
    }

    /**
     * Get user profile with statistics
     */
    public function getUserProfile(Request $request)
    {
        $user = $request->user();

        // Refresh user data
        $user->refresh();

        // Calculate statistics
        $totalScans = History::where('user_id', $user->id)->where('status', 'success')->count();
        $totalWasteWeight = History::where('user_id', $user->id)->sum('weight');
        $totalCoinsEarned = History::where('user_id', $user->id)->sum('coins_earned');
        $totalTransactions = Transaction::where('user_id', $user->id)->count();

        // Convert decimal to float
        $totalWasteWeight = $totalWasteWeight ? (float) $totalWasteWeight : 0.0;

        return response()->json([
            'success' => true,
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'balance_rp' => $user->balance_rp ?? 0,
                    'balance_coins' => $user->balance_coins ?? 0,
                    'eco_coins' => $user->balance_coins ?? 0, // alias
                    'formatted_balance_rp' => 'Rp ' . number_format((float) ($user->balance_rp ?? 0), 0, ',', '.'),
                    'member_since' => $user->created_at,
                ],
                'statistics' => [
                    'total_scans' => $totalScans,
                    'total_waste_weight' => round($totalWasteWeight, 2),
                    'total_coins_earned' => $totalCoinsEarned,
                    'total_transactions' => $totalTransactions,
                    'environmental_impact' => [
                        'co2_saved' => round($totalWasteWeight * 0.5, 2), // kg CO2
                        'trees_equivalent' => round($totalWasteWeight * 0.1, 1), // pohon
                    ]
                ]
            ]
        ]);
    }

    /**
     * Helper function to get transaction type label
     */
    private function getTypeLabel($type)
    {
        $labels = [
            'topup' => 'Top Up',
            'manual_topup' => 'Top Up Manual',
            'coin_exchange_to_rp' => 'Tukar Koin',
            'scan_reward' => 'Reward Scan',
            'transfer_out' => 'Transfer Keluar',
            'transfer_in' => 'Transfer Masuk',
        ];

        return $labels[$type] ?? ucfirst(str_replace('_', ' ', $type));
    }

    /**
     * Helper function to check if transaction is income
     */
    private function isIncomeTransaction($transaction)
    {
        return in_array($transaction->type, ['topup', 'manual_topup', 'scan_reward', 'transfer_in']) ||
               ($transaction->amount_rp && $transaction->amount_rp > 0) ||
               ($transaction->amount_coins && $transaction->amount_coins > 0);
    }
}
