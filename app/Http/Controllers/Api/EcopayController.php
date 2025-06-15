<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Transaction;

class EcopayController extends Controller
{
    // ... method getWallet dan getTransactions yang sudah ada ...

    // METHOD BARU UNTUK TUKAR KOIN
    public function exchangeCoins(Request $request)
    {
        $validated = $request->validate([
            'coins_to_exchange' => 'required|integer|min:1',
        ]);

        $user = $request->user();
        $coinsToExchange = $validated['coins_to_exchange'];

        // Cek apakah koin user mencukupi
        if ($user->balance_coins < $coinsToExchange) {
            return response()->json(['message' => 'Koin Anda tidak mencukupi.'], 422); // Unprocessable Entity
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
                    'amount_coins' => -$coinsToExchange, // Diberi tanda minus karena koin berkurang
                    'description' => "$coinsToExchange koin ditukar menjadi Rp. " . number_format($rpAmount, 0, ',', '.'),
                ]);
            });
        } catch (\Exception $e) {
            return response()->json(['message' => 'Gagal menukar koin, silakan coba lagi.', 'error' => $e->getMessage()], 500);
        }

        return response()->json(['message' => 'Koin berhasil ditukarkan!']);
    }

    // ... di dalam class EcopayController ...
// ... setelah method exchangeCoins ...

public function topup(Request $request)
{
    $validated = $request->validate([
        'amount' => 'required|integer|min:1000', // Minimal top-up 1000
    ]);

    $user = $request->user();
    $amount = $validated['amount'];

    try {
        DB::transaction(function () use ($user, $amount) {
            // 1. Tambah saldo Rupiah user
            $user->increment('balance_rp', $amount);

            // 2. Catat transaksi
            Transaction::create([
                'user_id' => $user->id,
                'type' => 'topup',
                'amount_rp' => $amount,
                'description' => "Top up saldo sebesar Rp. " . number_format($amount, 0, ',', '.'),
            ]);
        });
    } catch (\Exception $e) {
        return response()->json(['message' => 'Gagal top up saldo.', 'error' => $e->getMessage()], 500);
    }

    return response()->json(['message' => 'Top up berhasil!']);
}
    // ... setelah method topup ...
public function transfer(Request $request)
{
    $validated = $request->validate([
        'amount' => 'required|integer|min:100', // Minimal transfer 100
        'destination' => 'required|string', // Untuk simulasi, kita hanya terima string
    ]);

    $user = $request->user();
    $amount = $validated['amount'];

    // Cek apakah saldo mencukupi
    if ($user->balance_rp < $amount) {
        return response()->json(['message' => 'Saldo Anda tidak mencukupi.'], 422);
    }

    try {
        DB::transaction(function () use ($user, $amount, $validated) {
            // 1. Kurangi saldo Rupiah user
            $user->decrement('balance_rp', $amount);

            // 2. Catat transaksi
            Transaction::create([
                'user_id' => $user->id,
                'type' => 'transfer_out',
                'amount_rp' => -$amount, // Diberi tanda minus karena saldo berkurang
                'description' => "Transfer ke {$validated['destination']}",
            ]);
        });
    } catch (\Exception $e) {
        return response()->json(['message' => 'Gagal melakukan transfer.', 'error' => $e->getMessage()], 500);
    }

    return response()->json(['message' => 'Transfer berhasil!']);
}
}
