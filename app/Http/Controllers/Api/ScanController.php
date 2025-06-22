<?php

// app/Http/Controllers/Api/ScanController.php - PERBAIKAN LENGKAP
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
            'weight' => 'required|numeric|min:0.1|max:100', // Berat antara 0.1 - 100kg
        ]);

        $user = $request->user();

        // Cari dropbox berdasarkan kode atau ambil yang pertama jika tidak ada sistem kode
        $dropbox = Dropbox::where('status', 'active')->first();

        if (!$dropbox) {
            return response()->json([
                'message' => 'Dropbox tidak ditemukan atau sedang maintenance.',
                'success' => false
            ], 404);
        }

        // Logika Poin berdasarkan jenis sampah dan berat
        $pointsPerGram = $this->getPointsPerGram($validated['waste_type']);
        $coins_awarded = (int)floor($validated['weight'] * 1000 * $pointsPerGram); // Convert kg to gram

        try {
            DB::transaction(function () use ($user, $dropbox, $validated, $coins_awarded) {
                // 1. Tambah koin ke user
                $user->increment('balance_coins', $coins_awarded);

                // 2. Catat ke history
                History::create([
                    'user_id' => $user->id,
                    'dropbox_id' => $dropbox->id,
                    'waste_type' => $validated['waste_type'],
                    'weight' => $validated['weight'],
                    'coins_earned' => $coins_awarded,
                    'status' => 'success',
                    'scan_time' => now(),
                ]);

                // 3. Catat ke transaksi
                Transaction::create([
                    'user_id' => $user->id,
                    'type' => 'scan_reward',
                    'amount_coins' => $coins_awarded,
                    'description' => "Scan sampah {$validated['waste_type']} seberat {$validated['weight']}kg",
                ]);
            });

            return response()->json([
                'message' => "Scan berhasil! Anda mendapat {$coins_awarded} koin!",
                'success' => true,
                'data' => [
                    'coins_earned' => $coins_awarded,
                    'waste_type' => $validated['waste_type'],
                    'weight' => $validated['weight'],
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
                // Log history error jika perlu
            }

            return response()->json([
                'message' => 'Transaksi gagal, silakan coba lagi.',
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get scan history for user
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
            'data' => $history
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

        return response()->json([
            'success' => true,
            'data' => [
                'total_scans' => $totalScans,
                'successful_scans' => $successfulScans,
                'total_coins_earned' => $totalCoinsEarned,
                'total_waste_weight' => round($totalWasteWeight, 2),
                'success_rate' => $totalScans > 0 ? round(($successfulScans / $totalScans) * 100, 1) : 0,
            ]
        ]);
    }

    /**
     * Calculate points per gram based on waste type
     */
    private function getPointsPerGram($wasteType)
    {
        $pointsMap = [
            'plastic' => 0.01,  // 10 koin per kg
            'paper' => 0.008,   // 8 koin per kg
            'metal' => 0.015,   // 15 koin per kg
            'glass' => 0.012,   // 12 koin per kg
            'organic' => 0.005, // 5 koin per kg
        ];

        return $pointsMap[$wasteType] ?? 0.01; // Default 10 koin per kg
    }
}
