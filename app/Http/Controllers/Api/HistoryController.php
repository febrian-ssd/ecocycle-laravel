<?php
// app/Http/Controllers/Api/HistoryController.php - CLEAN SINGLE FILE

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
