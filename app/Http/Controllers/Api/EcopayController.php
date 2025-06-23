<?php
// app/Http/Controllers/Api/EcopayController.php - FIXED TRANSFER

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Transaction;
use App\Models\User;

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

        return response()->json([
            'data' => $transactions
        ]);
    }

    /**
     * Exchange coins to Rupiah
     */
    public function exchangeCoins(Request $request)
    {
        $validated = $request->validate([
            'coins' => 'required|integer|min:1',
        ]);

        $user = $request->user();
        $coinsToExchange = $validated['coins'];

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
     * Transfer saldo - FIXED VERSION
     */
    public function transfer(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email|max:255',
            'amount' => 'required|numeric|min:1000',
            'description' => 'nullable|string|max:255',
        ]);

        $user = $request->user();
        $amount = (float) $validated['amount'];
        $recipientEmail = $validated['email'];

        // Cek apakah saldo mencukupi
        if ($user->balance_rp < $amount) {
            return response()->json([
                'message' => 'Saldo Anda tidak mencukupi.',
                'success' => false
            ], 422);
        }

        // Cari penerima berdasarkan email
        $recipient = User::where('email', $recipientEmail)->first();
        if (!$recipient) {
            return response()->json([
                'message' => 'Email penerima tidak terdaftar di sistem.',
                'success' => false
            ], 404);
        }

        // Tidak bisa transfer ke diri sendiri
        if ($recipient->id === $user->id) {
            return response()->json([
                'message' => 'Anda tidak dapat transfer ke diri sendiri.',
                'success' => false
            ], 422);
        }

        try {
            DB::transaction(function () use ($user, $recipient, $amount, $validated) {
                // 1. Kurangi saldo pengirim
                $user->decrement('balance_rp', $amount);

                // 2. Tambah saldo penerima
                $recipient->increment('balance_rp', $amount);

                // 3. Catat transaksi untuk pengirim
                Transaction::create([
                    'user_id' => $user->id,
                    'type' => 'transfer_out',
                    'amount_rp' => -$amount,
                    'description' => "Transfer ke {$recipient->name} ({$recipient->email}) - " . ($validated['description'] ?? ''),
                ]);

                // 4. Catat transaksi untuk penerima
                Transaction::create([
                    'user_id' => $recipient->id,
                    'type' => 'transfer_in',
                    'amount_rp' => $amount,
                    'description' => "Transfer dari {$user->name} ({$user->email}) - " . ($validated['description'] ?? ''),
                ]);
            });

            return response()->json([
                'message' => 'Transfer berhasil!',
                'success' => true,
                'data' => [
                    'amount_transferred' => $amount,
                    'recipient_name' => $recipient->name,
                    'recipient_email' => $recipient->email,
                    'new_balance_rp' => $user->fresh()->balance_rp,
                    'description' => $validated['description'] ?? '',
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

        // Total income (topup + rewards + transfer in)
        $totalIncome = Transaction::where('user_id', $user->id)
            ->whereIn('type', ['topup', 'manual_topup', 'scan_reward', 'transfer_in'])
            ->where('amount_rp', '>', 0)
            ->sum('amount_rp');

        // Total spending (transfers + exchanges)
        $totalSpending = abs(Transaction::where('user_id', $user->id)
            ->whereIn('type', ['transfer_out', 'coin_exchange_to_rp'])
            ->where('amount_rp', '<', 0)
            ->sum('amount_rp'));

        // Total coins earned
        $totalCoinsEarned = Transaction::where('user_id', $user->id)
            ->where('type', 'scan_reward')
            ->where('amount_coins', '>', 0)
            ->sum('amount_coins');

        return response()->json([
            'current_balance_rp' => $user->balance_rp ?? 0,
            'current_balance_coins' => $user->balance_coins ?? 0,
            'total_income' => $totalIncome,
            'total_spending' => $totalSpending,
            'total_coins_earned' => $totalCoinsEarned,
            'transaction_count' => Transaction::where('user_id', $user->id)->count(),
        ]);
    }

    /**
     * Create topup request
     */
    public function createTopupRequest(Request $request)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:10000|max:10000000',
            'payment_method' => 'nullable|string|max:100',
            'user_note' => 'nullable|string|max:500',
        ]);

        $user = $request->user();

        try {
            $topupRequest = \App\Models\TopupRequest::create([
                'user_id' => $user->id,
                'amount' => $validated['amount'],
                'status' => 'pending',
                'type' => 'request',
                'payment_method' => $validated['payment_method'] ?? 'transfer_bank',
                'user_note' => $validated['user_note'] ?? '',
            ]);

            return response()->json([
                'message' => 'Permintaan top up berhasil dibuat. Silakan tunggu konfirmasi admin.',
                'success' => true,
                'data' => [
                    'request_id' => $topupRequest->id,
                    'amount' => $topupRequest->amount,
                    'status' => $topupRequest->status,
                    'created_at' => $topupRequest->created_at,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Gagal membuat permintaan top up.',
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get user topup requests
     */
    public function getTopupRequests(Request $request)
    {
        $user = $request->user();

        $requests = \App\Models\TopupRequest::where('user_id', $user->id)
                    ->orderBy('created_at', 'desc')
                    ->limit(20)
                    ->get();

        return response()->json([
            'data' => $requests
        ]);
    }
}
