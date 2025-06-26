<?php
// routes/api.php - PERBAIKAN LENGKAP

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DropboxController;
use App\Http\Controllers\Api\ScanController;
use App\Http\Controllers\Api\EcopayController;
use App\Http\Controllers\Api\HistoryController;
use App\Http\Controllers\Api\AdminController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// ========================================================================
// RUTE PUBLIK (Tidak Perlu Login)
// ========================================================================
// Rute untuk login dan register HARUS berada di sini, di luar middleware auth.
Route::get('health', function () {
    return response()->json([
        'status' => 'ok',
        'timestamp' => now(),
        'version' => '1.0.0'
    ]);
});

Route::post('login', [AuthController::class, 'login']);
Route::post('register', [AuthController::class, 'register']);

// Rute untuk menampilkan lokasi dropbox di peta (bisa diakses publik)
Route::get('dropboxes', [DropboxController::class, 'index']);


// ========================================================================
// RUTE YANG DILINDUNGI (Wajib Login)
// ========================================================================
// Semua rute di dalam grup ini hanya bisa diakses setelah user berhasil login.
Route::middleware(['auth:sanctum'])->group(function () {

    // --- Rute Umum untuk semua user yang sudah login ---
    Route::get('auth/user', [AuthController::class, 'user']);
    Route::post('auth/logout', [AuthController::class, 'logout']);
    Route::get('auth/check-token', [AuthController::class, 'checkToken']);

    // --- Rute Khusus untuk Role 'user' ---
    Route::middleware(['role:user'])->prefix('user')->group(function () {
        // Kelola profil user
        Route::get('profile', [HistoryController::class, 'getUserProfile']);
        Route::put('profile', function(Request $request) {
            // Logika update profil Anda
            $user = $request->user();
            $validated = $request->validate([
                'name' => 'sometimes|string|max:255',
                'password' => 'sometimes|string|min:6|confirmed',
            ]);
            if (isset($validated['password'])) {
                $validated['password'] = \Illuminate\Support\Facades\Hash::make($validated['password']);
            }
            $user->update($validated);
            return response()->json(['success' => true, 'message' => 'Profile updated successfully']);
        });

        Route::get('transactions', [HistoryController::class, 'getTransactions']);
        Route::post('topup', [EcopayController::class, 'createTopupRequest']);
        Route::post('exchange-coins', [EcopayController::class, 'exchangeCoins']);
        Route::get('history', [HistoryController::class, 'getHistory']);

        // Rute terkait Ecopay / Wallet
        Route::get('wallet', [EcopayController::class, 'getWallet']);
        Route::get('transactions', [EcopayController::class, 'getTransactions']);
        Route::post('transfer', [EcopayController::class, 'transfer']);
        Route::post('exchange-coins', [EcopayController::class, 'exchangeCoins']);

        // Rute terkait Scan
        Route::post('scan/confirm', [ScanController::class, 'confirmScan']);
        Route::get('scan/history', [ScanController::class, 'getScanHistory']);
        Route::get('scan/stats', [ScanController::class, 'getScanStats']);

        // Dan rute-rute user lainnya...
    });

    // --- Rute Khusus untuk Role 'admin' ---
    Route::middleware(['role:admin'])->prefix('admin')->group(function () {
        Route::get('dashboard', [AdminController::class, 'dashboard']);
        // Dan rute-rute admin lainnya...
    });
});


// Fallback jika endpoint tidak ditemukan
Route::fallback(function () {
    return response()->json([
        'success' => false,
        'message' => 'API endpoint not found',
    ], 404);
});
