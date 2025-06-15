<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class EcopayController extends Controller
{
    // Mengembalikan saldo dan koin user yang sedang login
    public function getWallet(Request $request)
    {
        $user = $request->user();
        return response()->json([
            'balance_rp' => $user->balance_rp,
            'balance_coins' => $user->balance_coins,
        ]);
    }

    // Mengembalikan riwayat transaksi user yang sedang login
    public function getTransactions(Request $request)
    {
        $transactions = $request->user()->transactions()->latest()->get();
        return response()->json($transactions);
    }
}
