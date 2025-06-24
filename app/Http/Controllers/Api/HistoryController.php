<?php
// app/Http/Controllers/Api/HistoryController.php - IMPROVED VERSION

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\History;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;

class HistoryController extends Controller
{
    /**
     * Get user scan history with better error handling
     */
    public function getScanHistory(Request $request)
    {
        try {
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
                        'weight' => (float) ($item->weight ?? 0),
                        'weight_g' => (float) ($item->weight_g ?? $item->weight ?? 0),
                        'coins_earned' => (int) ($item->coins_earned ?? 0),
                        'eco_coins' => (int) ($item->coins_earned ?? 0), // Alias
                        'status' => $item->status ?? 'success',
                        'scan_time' => $item->scan_time ?? $item->created_at,
                        'created_at' => $item->created_at,
                        'activity_type' => $item->activity_type ?? 'scan',
                        'type' => $item->type ?? 'scan',
                        'dropbox' => $item->dropbox ? [
                            'id' => $item->dropbox->id,
                            'location_name' => $item->dropbox->location_name,
                            'latitude' => (float) $item->dropbox->latitude,
                            'longitude' => (float) $item->dropbox->longitude,
                        ] : [
                            'id' => $item->dropbox_id ?? null,
                            'location_name' => $item->dropbox_location ?? 'Unknown Location',
                            'latitude' => 0.0,
                            'longitude' => 0.0,
                        ],
                    ];
                }),
                'meta' => [
                    'total' => $history->count(),
                    'page' => 1,
                    'per_page' => 50,
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('getScanHistory error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil riwayat scan',
                'data' => [],
                'meta' => ['total' => 0]
            ], 500);
        }
    }

    /**
     * Get user scan statistics with better error handling
     */
    public function getScanStats(Request $request)
    {
        try {
            $user = $request->user();

            // Use DB query with null safety
            $stats = DB::table('histories')
                ->where('user_id', $user->id)
                ->selectRaw('
                    COUNT(*) as total_scans,
                    COUNT(CASE WHEN status = "success" THEN 1 END) as successful_scans,
                    COALESCE(SUM(CASE WHEN coins_earned IS NOT NULL THEN coins_earned ELSE 0 END), 0) as total_coins_earned,
                    COALESCE(SUM(CASE WHEN weight IS NOT NULL THEN weight WHEN weight_g IS NOT NULL THEN weight_g ELSE 0 END), 0) as total_waste_weight
                ')
                ->first();

            // Weekly and monthly stats
            $weeklyScans = DB::table('histories')
                ->where('user_id', $user->id)
                ->where('created_at', '>=', now()->startOfWeek())
                ->where(function($query) {
                    $query->where('status', 'success')->orWhereNull('status');
                })
                ->count();

            $monthlyScans = DB::table('histories')
                ->where('user_id', $user->id)
                ->where('created_at', '>=', now()->startOfMonth())
                ->where(function($query) {
                    $query->where('status', 'success')->orWhereNull('status');
                })
                ->count();

            // Waste type breakdown with null safety
            $wasteTypeStats = DB::table('histories')
                ->where('user_id', $user->id)
                ->where(function($query) {
                    $query->where('status', 'success')->orWhereNull('status');
                })
                ->selectRaw('
                    COALESCE(waste_type, "plastic") as waste_type,
                    COUNT(*) as count,
                    COALESCE(SUM(CASE WHEN weight IS NOT NULL THEN weight WHEN weight_g IS NOT NULL THEN weight_g ELSE 0 END), 0) as total_weight,
                    COALESCE(SUM(CASE WHEN coins_earned IS NOT NULL THEN coins_earned ELSE 0 END), 0) as total_coins
                ')
                ->groupBy('waste_type')
                ->get();

            $totalScans = (int) $stats->total_scans;
            $successfulScans = (int) $stats->successful_scans;
            $totalCoinsEarned = (int) $stats->total_coins_earned;
            $totalWasteWeight = (float) $stats->total_waste_weight;

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

        } catch (\Exception $e) {
            \Log::error('getScanStats error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil statistik scan',
                'data' => [
                    'total_scans' => 0,
                    'successful_scans' => 0,
                    'total_coins_earned' => 0,
                    'total_waste_weight' => 0.0,
                    'success_rate' => 0,
                    'weekly_scans' => 0,
                    'monthly_scans' => 0,
                    'waste_type_breakdown' => [],
                ]
            ], 500);
        }
    }

    /**
     * Get user transaction history with better formatting and error handling
     */
    public function getTransactionHistory(Request $request)
    {
        try {
            $user = $request->user();

            $transactions = Transaction::where('user_id', $user->id)
                                     ->orderBy('created_at', 'desc')
                                     ->limit(50)
                                     ->get();

            return response()->json([
                'success' => true,
                'data' => $transactions->map(function($transaction) {
                    $amountRp = (float) ($transaction->amount_rp ?? 0);
                    $amountCoins = (int) ($transaction->amount_coins ?? 0);
                    $isIncome = $this->isIncomeTransaction($transaction);

                    return [
                        'id' => $transaction->id,
                        'type' => $transaction->type,
                        'amount_rp' => $amountRp,
                        'amount_coins' => $amountCoins,
                        'description' => $transaction->description ?? '',
                        'created_at' => $transaction->created_at,
                        'formatted_amount_rp' => $amountRp != 0 ?
                            ($isIncome ? '+' : '') . 'Rp ' . number_format(abs($amountRp), 0, ',', '.') :
                            null,
                        'formatted_amount_coins' => $amountCoins != 0 ?
                            ($amountCoins >= 0 ? '+' : '') . $amountCoins . ' koin' :
                            null,
                        'type_label' => $this->getTypeLabel($transaction->type),
                        'is_income' => $isIncome,
                    ];
                }),
                'meta' => [
                    'total' => $transactions->count(),
                    'page' => 1,
                    'per_page' => 50,
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('getTransactionHistory error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil riwayat transaksi',
                'data' => [],
                'meta' => ['total' => 0]
            ], 500);
        }
    }

    /**
     * Get general history (fallback method)
     */
    public function getHistory(Request $request)
    {
        try {
            $user = $request->user();

            // Combine both histories and transactions
            $histories = History::where('user_id', $user->id)
                               ->orderBy('created_at', 'desc')
                               ->limit(50)
                               ->get()
                               ->map(function($item) {
                                   return [
                                       'id' => $item->id,
                                       'type' => $item->type ?? 'scan',
                                       'activity_type' => $item->activity_type ?? 'scan',
                                       'waste_type' => $item->waste_type ?? 'plastic',
                                       'weight' => (float) ($item->weight ?? 0),
                                       'weight_g' => (float) ($item->weight_g ?? $item->weight ?? 0),
                                       'coins_earned' => (int) ($item->coins_earned ?? 0),
                                       'eco_coins' => (int) ($item->coins_earned ?? 0),
                                       'status' => $item->status ?? 'success',
                                       'created_at' => $item->created_at,
                                       'dropbox_id' => $item->dropbox_id,
                                       'dropbox_location' => $item->dropbox?->location_name ?? 'Unknown',
                                   ];
                               });

            return response()->json([
                'success' => true,
                'data' => $histories
            ]);

        } catch (\Exception $e) {
            \Log::error('getHistory error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil riwayat',
                'data' => []
            ], 500);
        }
    }

    /**
     * Get user profile with statistics
     */
    public function getUserProfile(Request $request)
    {
        try {
            $user = $request->user();

            // Refresh user data
            $user->refresh();

            // Calculate statistics with null safety
            $totalScans = History::where('user_id', $user->id)
                                ->where(function($query) {
                                    $query->where('status', 'success')->orWhereNull('status');
                                })
                                ->count();

            $totalWasteWeight = (float) History::where('user_id', $user->id)->sum('weight');
            $totalCoinsEarned = (int) History::where('user_id', $user->id)->sum('coins_earned');
            $totalTransactions = Transaction::where('user_id', $user->id)->count();

            return response()->json([
                'success' => true,
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'balance_rp' => (float) ($user->balance_rp ?? 0),
                        'balance_coins' => (int) ($user->balance_coins ?? 0),
                        'eco_coins' => (int) ($user->balance_coins ?? 0), // alias
                        'formatted_balance_rp' => 'Rp ' . number_format((float) ($user->balance_rp ?? 0), 0, ',', '.'),
                        'member_since' => $user->created_at,
                        'is_admin' => (bool) ($user->is_admin ?? false),
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

        } catch (\Exception $e) {
            \Log::error('getUserProfile error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil profil user',
                'data' => [
                    'user' => [
                        'id' => $request->user()->id,
                        'name' => $request->user()->name,
                        'email' => $request->user()->email,
                        'balance_rp' => 0,
                        'balance_coins' => 0,
                        'eco_coins' => 0,
                        'formatted_balance_rp' => 'Rp 0',
                    ],
                    'statistics' => [
                        'total_scans' => 0,
                        'total_waste_weight' => 0,
                        'total_coins_earned' => 0,
                        'total_transactions' => 0,
                    ]
                ]
            ], 500);
        }
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
        $incomeTypes = ['topup', 'manual_topup', 'scan_reward', 'transfer_in'];

        return in_array($transaction->type, $incomeTypes) ||
               ($transaction->amount_rp && $transaction->amount_rp > 0) ||
               ($transaction->amount_coins && $transaction->amount_coins > 0);
    }
}
