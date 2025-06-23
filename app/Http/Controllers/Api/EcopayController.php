<?php
// app/Http/Controllers/Api/EcopayController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Transaction;

class EcopayController extends Controller
{
    /**
     * Get user wallet information
     */
    public function getWallet(Request $request)
    {
        $user = $request->user();

        return response()->json([
            'balance_rp' => $user->balance_rp ?? 0,
            'balance_coins' => $user->balance_coins ?? 0,
            'formatted_balance_rp' => 'Rp ' . number_format($user->balance_rp ?? 0, 0, ',', '.'),
        ]);
    }

    /**
     * Get user transaction history
     */
    public function getTransactions(Request $request)
    {
        $user = $request->user();

        $transactions = Transaction::where('user_id', $user->id)
                                 ->orderBy('created_at', 'desc')
                                 ->limit(50)
                                 ->get();

        // Return as array directly for consistent parsing
        return response()->json($transactions);
    }

    /**
     * Exchange coins to Rupiah
     */
    public function exchangeCoins(Request $request)
    {
        $validated = $request->validate([
            'coins_to_exchange' => 'required|integer|min:1',
        ]);

        $user = $request->user();
        $coinsToExchange = $validated['coins_to_exchange'];

        // Cek apakah koin user mencukupi
        if ($user->balance_coins < $coinsToExchange) {
            return response()->json([
                'message' => 'Koin Anda tidak mencukupi.',
                'success' => false
            ], 422);
        }

        // Perhitungan: 1 koin = 100 Rupiah
        $rpAmount = $coinsToExchange * 100;

        try {
            DB::transaction(function () use ($user, $coinsToExchange, $rpAmount) {
                // 1. Kurangi saldo koin user
                $user->decrement('balance_coins', $coinsToExchange);

                // 2. Tambah saldo Rupiah user
                $user->increment('balance_rp', $rpAmount);

                // 3. Catat transaksi
                Transaction::create([
                    'user_id' => $user->id,
                    'type' => 'coin_exchange_to_rp',
                    'amount_rp' => $rpAmount,
                    'amount_coins' => -$coinsToExchange,
                    'description' => "$coinsToExchange koin ditukar menjadi Rp. " . number_format($rpAmount, 0, ',', '.'),
                ]);
            });

            return response()->json([
                'message' => 'Koin berhasil ditukarkan!',
                'success' => true,
                'data' => [
                    'coins_exchanged' => $coinsToExchange,
                    'rupiah_received' => $rpAmount,
                    'new_balance_rp' => $user->fresh()->balance_rp,
                    'new_balance_coins' => $user->fresh()->balance_coins,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Gagal menukar koin, silakan coba lagi.',
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Transfer saldo
     */
    public function transfer(Request $request)
    {
        $validated = $request->validate([
            'amount' => 'required|integer|min:100',
            'destination' => 'required|string|max:255',
        ]);

        $user = $request->user();
        $amount = $validated['amount'];

        // Cek apakah saldo mencukupi
        if ($user->balance_rp < $amount) {
            return response()->json([
                'message' => 'Saldo Anda tidak mencukupi.',
                'success' => false
            ], 422);
        }

        try {
            DB::transaction(function () use ($user, $amount, $validated) {
                // 1. Kurangi saldo Rupiah user
                $user->decrement('balance_rp', $amount);

                // 2. Catat transaksi
                Transaction::create([
                    'user_id' => $user->id,
                    'type' => 'transfer_out',
                    'amount_rp' => -$amount,
                    'description' => "Transfer ke {$validated['destination']} - Rp " . number_format($amount, 0, ',', '.'),
                ]);
            });

            return response()->json([
                'message' => 'Transfer berhasil!',
                'success' => true,
                'data' => [
                    'amount_transferred' => $amount,
                    'destination' => $validated['destination'],
                    'new_balance_rp' => $user->fresh()->balance_rp,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Gagal melakukan transfer.',
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get user balance summary
     */
    public function getBalanceSummary(Request $request)
    {
        $user = $request->user();

        // Total income (topup + rewards)
        $totalIncome = Transaction::where('user_id', $user->id)
            ->whereIn('type', ['topup', 'manual_topup', 'scan_reward'])
            ->where(function($query) {
                $query->where('amount_rp', '>', 0)
                      ->orWhere('amount_coins', '>', 0);
            })
            ->sum('amount_rp');

        // Total spending (transfers + exchanges)
        $totalSpending = Transaction::where('user_id', $user->id)
            ->whereIn('type', ['transfer_out', 'coin_exchange_to_rp'])
            ->where('amount_rp', '<', 0)
            ->sum('amount_rp');

        // Total coins earned
        $totalCoinsEarned = Transaction::where('user_id', $user->id)
            ->where('type', 'scan_reward')
            ->sum('amount_coins');

        return response()->json([
            'current_balance_rp' => $user->balance_rp ?? 0,
            'current_balance_coins' => $user->balance_coins ?? 0,
            'total_income' => abs($totalIncome),
            'total_spending' => abs($totalSpending),
            'total_coins_earned' => $totalCoinsEarned,
            'transaction_count' => Transaction::where('user_id', $user->id)->count(),
        ]);
    }
}
