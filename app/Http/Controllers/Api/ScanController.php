<?php
// app/Http/Controllers/Api/ScanController.php - FIXED VERSION

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Dropbox;
use App\Models\History;
use App\Models\Transaction;

class ScanController extends Controller
{
    public function confirmScan(Request $request)
    {
        // Validasi data yang dikirim dari Flutter
        $validated = $request->validate([
            'dropbox_code' => 'required|string',
            'waste_type' => 'required|string|in:plastic,paper,metal,glass,organic',
            'weight' => 'required|numeric|min:0.1|max:100',
        ]);

        $user = $request->user();

        // Cari dropbox berdasarkan kode atau ID
        $dropbox = Dropbox::where('id', $validated['dropbox_code'])
                         ->orWhere('location_name', 'LIKE', '%' . $validated['dropbox_code'] . '%')
                         ->where('status', 'active')
                         ->first();

        if (!$dropbox) {
            return response()->json([
                'success' => false,
                'message' => 'Dropbox tidak ditemukan atau sedang maintenance.'
            ], 404);
        }

        // Logika Poin berdasarkan jenis sampah dan berat (dalam gram)
        $pointsPerGram = $this->getPointsPerGram($validated['waste_type']);
        $weightInGrams = $validated['weight'] * 1000; // Convert kg to gram
        $coins_awarded = (int)floor($weightInGrams * $pointsPerGram);

        try {
            DB::transaction(function () use ($user, $dropbox, $validated, $coins_awarded, $weightInGrams) {
                // 1. Tambah koin ke user
                $user->increment('balance_coins', $coins_awarded);

                // 2. Catat ke history dengan data lebih lengkap
                History::create([
                    'user_id' => $user->id,
                    'dropbox_id' => $dropbox->id,
                    'waste_type' => $validated['waste_type'],
                    'weight' => $validated['weight'], // dalam kg
                    'coins_earned' => $coins_awarded,
                    'status' => 'success',
                    'scan_time' => now(),
                ]);

                // 3. Catat ke transaksi
                Transaction::create([
                    'user_id' => $user->id,
                    'type' => 'scan_reward',
                    'amount_coins' => $coins_awarded,
                    'description' => "Scan sampah {$validated['waste_type']} seberat {$validated['weight']}kg - Reward {$coins_awarded} koin",
                ]);
            });

            return response()->json([
                'success' => true,
                'message' => "Scan berhasil! Anda mendapat {$coins_awarded} koin!",
                'data' => [
                    'coins_earned' => $coins_awarded,
                    'waste_type' => $validated['waste_type'],
                    'weight' => $validated['weight'],
                    'weight_grams' => $weightInGrams,
                    'new_balance_coins' => $user->fresh()->balance_coins,
                    'dropbox_location' => $dropbox->location_name,
                ]
            ]);

        } catch (\Exception $e) {
            // Jika gagal, catat ke history sebagai failed
            try {
                History::create([
                    'user_id' => $user->id,
                    'dropbox_id' => $dropbox->id,
                    'waste_type' => $validated['waste_type'],
                    'weight' => $validated['weight'],
                    'coins_earned' => 0,
                    'status' => 'failed',
                    'scan_time' => now(),
                    'error_message' => $e->getMessage(),
                ]);
            } catch (\Exception $historyError) {
                \Log::error('Failed to save scan history: ' . $historyError->getMessage());
            }

            return response()->json([
                'success' => false,
                'message' => 'Transaksi gagal, silakan coba lagi.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Calculate points per gram based on waste type
     */
    private function getPointsPerGram($wasteType)
    {
        $pointsMap = [
            'plastic' => 0.01,  // 10 koin per kg (0.01 per gram)
            'paper' => 0.008,   // 8 koin per kg
            'metal' => 0.015,   // 15 koin per kg
            'glass' => 0.012,   // 12 koin per kg
            'organic' => 0.005, // 5 koin per kg
        ];

        return $pointsMap[$wasteType] ?? 0.01; // Default 10 koin per kg
    }
}
