<?php
// app/Http/Controllers/Api/ScanController.php

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

        // Cari dropbox berdasarkan kode atau ambil yang pertama jika tidak ada sistem kode
        $dropbox = Dropbox::where('status', 'active')->first();

        if (!$dropbox) {
            return response()->json([
                'message' => 'Dropbox tidak ditemukan atau sedang maintenance.',
                'success' => false
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
                'message' => "Scan berhasil! Anda mendapat {$coins_awarded} koin!",
                'success' => true,
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

        // Statistik per jenis sampah
        $wasteTypeStats = History::where('user_id', $user->id)
            ->where('status', 'success')
            ->selectRaw('waste_type, COUNT(*) as count, SUM(weight) as total_weight, SUM(coins_earned) as total_coins')
            ->groupBy('waste_type')
            ->get();

        // Statistik bulanan (6 bulan terakhir)
        $monthlyStats = History::where('user_id', $user->id)
            ->where('status', 'success')
            ->where('created_at', '>=', now()->subMonths(6))
            ->selectRaw('YEAR(created_at) as year, MONTH(created_at) as month, COUNT(*) as scans, SUM(coins_earned) as coins')
            ->groupBy('year', 'month')
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'total_scans' => $totalScans,
                'successful_scans' => $successfulScans,
                'total_coins_earned' => $totalCoinsEarned,
                'total_waste_weight' => round($totalWasteWeight, 2),
                'success_rate' => $totalScans > 0 ? round(($successfulScans / $totalScans) * 100, 1) : 0,
                'waste_type_breakdown' => $wasteTypeStats,
                'monthly_stats' => $monthlyStats,
            ]
        ]);
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
