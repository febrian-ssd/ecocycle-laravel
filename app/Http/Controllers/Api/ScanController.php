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

// app/Http/Controllers/Api/ScanController.php

public function confirmScan(Request $request)
{
    // Validasi data yang dikirim dari Flutter
    $validated = $request->validate([
        'dropbox_code' => 'required|string',
        'waste_type' => 'required|string',
        'weight' => 'required|numeric|min:0', // Berat tidak boleh minus
    ]);

    $user = $request->user();
    $dropbox = Dropbox::first(); // Asumsi dropbox ditemukan
    if (!$dropbox) {
        return response()->json(['message' => 'Dropbox not found'], 404);
    }

    // Logika Poin: 1 gram = 10 koin
    $coins_awarded = (int)floor($validated['weight'] * 10);

    // ... (sisa kode transaksi database tidak berubah) ...

    try {
        DB::transaction(function () use ($user, $dropbox, $validated, $coins_awarded) {
            $user->increment('balance_coins', $coins_awarded);
            History::create([/* ... */]);
            Transaction::create([/* ... */]);
        });
    } catch (\Exception $e) {
        return response()->json(['message' => 'Transaction failed', 'error' => $e->getMessage()], 500);
    }

    return response()->json(['message' => 'Scan confirmed successfully, you earned ' . $coins_awarded . ' coins!']);
}
}
