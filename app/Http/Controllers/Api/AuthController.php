<?php

// app/Http/Controllers/Api/AuthController.php - PERBAIKAN
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use App\Models\User;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
        ]);

        $user = User::create([
            'name' => $validatedData['name'],
            'email' => $validatedData['email'],
            'password' => Hash::make($validatedData['password']),
            'is_admin' => false, // Default user bukan admin
            'balance_rp' => 0, // Set default balance
            'balance_coins' => 0, // Set default coins
        ]);

        // Buat token untuk user yang baru mendaftar
        $token = $user->createToken('auth_token')->plainTextToken;

        // Kembalikan data user dan token dalam format JSON
        return response()->json([
            'message' => 'Registration successful',
            'user' => $user,
            'access_token' => $token,
            'token_type' => 'Bearer',
        ], 201);
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (!Auth::attempt($credentials)) {
            throw ValidationException::withMessages([
                'email' => ['These credentials do not match our records.'],
            ]);
        }

        $user = User::where('email', $request->email)->firstOrFail();
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'user' => $user,
            'access_token' => $token,
            'token_type' => 'Bearer',
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Logout successful']);
    }
}

// ======================================================================

// app/Http/Controllers/Api/EcopayController.php - PERBAIKAN
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
            return response()->json(['message' => 'Koin Anda tidak mencukupi.'], 422);
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
        } catch (\Exception $e) {
            return response()->json(['message' => 'Gagal menukar koin, silakan coba lagi.', 'error' => $e->getMessage()], 500);
        }

        return response()->json([
            'message' => 'Koin berhasil ditukarkan!',
            'new_balance_rp' => $user->fresh()->balance_rp,
            'new_balance_coins' => $user->fresh()->balance_coins,
        ]);
    }

    /**
     * Top up saldo Rupiah
     */
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

        return response()->json([
            'message' => 'Top up berhasil!',
            'new_balance_rp' => $user->fresh()->balance_rp,
        ]);
    }

    /**
     * Transfer saldo
     */
    public function transfer(Request $request)
    {
        $validated = $request->validate([
            'amount' => 'required|integer|min:100',
            'destination' => 'required|string',
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
                    'amount_rp' => -$amount,
                    'description' => "Transfer ke {$validated['destination']}",
                ]);
            });
        } catch (\Exception $e) {
            return response()->json(['message' => 'Gagal melakukan transfer.', 'error' => $e->getMessage()], 500);
        }

        return response()->json([
            'message' => 'Transfer berhasil!',
            'new_balance_rp' => $user->fresh()->balance_rp,
        ]);
    }
}
