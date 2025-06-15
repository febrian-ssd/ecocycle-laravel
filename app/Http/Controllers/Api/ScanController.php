<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Dropbox;
use App\Models\History;
use App\Models\Transaction;

class ScanController extends Controller
{
   // app/Http/Controllers/Api/ScanController.php

public function confirmScan(Request $request)
{
    // ... (Validasi data) ...

    // 1. Mengambil data user yang sedang login (berdasarkan token API)
    $user = $request->user();

    // 2. Menemukan data dropbox (untuk contoh kita ambil yang pertama)
    $dropbox = Dropbox::first();
    if (!$dropbox) {
        return response()->json(['message' => 'Dropbox not found'], 404);
    }

    // 3. Menghitung koin yang didapat (10 gram = 1 koin)
    $coins_awarded = (int)floor($request->weight / 10);

    // 4. Memulai transaksi database yang aman
    try {
        DB::transaction(function () use ($user, $dropbox, $request, $coins_awarded) {

            // === INI BAGIAN UTAMANYA ===
            // Menambah nilai kolom 'balance_coins' pada user dengan jumlah koin yang didapat
            $user->increment('balance_coins', $coins_awarded);

            // Mencatat ke tabel History (untuk Riwayat Scan)
                History::create([
                    'user_id' => $user->id,
                    'dropbox_id' => $dropbox->id,
                    'status' => 'success',
                ]);

            // Mencatat ke tabel Transactions (untuk Riwayat EcoPay)
                Transaction::create([
                    'user_id' => $user->id,
                    'type' => 'scan_reward',
                    'amount_coins' => $coins_awarded,
                    'description' => "Reward dari scan sampah {$request->waste_type}",
             ]);
            });
        } catch (\Exception $e) {
        // ... (handle error) ...
        }

        return response()->json(['message' => 'Scan confirmed successfully!']);
    }
}
