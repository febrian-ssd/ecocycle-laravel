<?php
// routes/api.php - FIXED VERSION

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
// HEALTH CHECK ROUTE
// ========================================================================
Route::get('health', function () {
    return response()->json([
        'status' => 'ok',
        'timestamp' => now(),
        'version' => '1.0.0'
    ]);
});

// ========================================================================
// RUTE PUBLIK (Tidak Perlu Login)
// ========================================================================
Route::post('login', [AuthController::class, 'login']);
Route::post('register', [AuthController::class, 'register']);

// Rute untuk menampilkan lokasi dropbox di peta (bisa diakses publik)
Route::get('dropboxes', [DropboxController::class, 'index']);
Route::get('dropboxes/nearby', [DropboxController::class, 'getNearby']);
Route::get('dropboxes/{id}', [DropboxController::class, 'show']);

// ========================================================================
// RUTE YANG DILINDUNGI (Wajib Login)
// ========================================================================
Route::middleware(['auth:sanctum'])->group(function () {

    // --- Rute Autentikasi ---
    Route::prefix('auth')->group(function () {
        Route::get('user', [AuthController::class, 'user']);
        Route::post('logout', [AuthController::class, 'logout']);
        Route::post('logout-all', [AuthController::class, 'logoutAll']);
        Route::get('check-token', [AuthController::class, 'checkToken']);
    });

    // --- Rute Khusus untuk Role 'user' ---
    Route::middleware(['role:user'])->prefix('user')->group(function () {
        // Profile Management
        Route::get('profile', [HistoryController::class, 'getUserProfile']);
        Route::put('profile', function(Request $request) {
            $user = $request->user();
            $validated = $request->validate([
                'name' => 'sometimes|string|max:255',
                'password' => 'sometimes|string|min:6|confirmed',
            ]);
            if (isset($validated['password'])) {
                $validated['password'] = \Illuminate\Support\Facades\Hash::make($validated['password']);
            }
            $user->update($validated);
            return response()->json([
                'success' => true,
                'message' => 'Profile updated successfully',
                'data' => $user
            ]);
        });

        // Wallet & Transactions
        Route::get('wallet', [EcopayController::class, 'getWallet']);
        Route::get('transactions', [EcopayController::class, 'getTransactions']);
        Route::post('transfer', [EcopayController::class, 'transfer']);
        Route::post('exchange-coins', [EcopayController::class, 'exchangeCoins']);

        // Topup
        Route::post('topup', [EcopayController::class, 'createTopupRequest']);
        Route::get('topup-requests', [EcopayController::class, 'getTopupRequests']);

        // Scan
        Route::post('scan/confirm', [ScanController::class, 'confirmScan']);
        Route::get('scan/history', [HistoryController::class, 'getScanHistory']);
        Route::get('scan/stats', [HistoryController::class, 'getScanStats']);

        // History
        Route::get('history', [HistoryController::class, 'getHistory']);
    });

    // --- Rute Khusus untuk Role 'admin' ---
    Route::middleware(['role:admin'])->prefix('admin')->group(function () {
        Route::get('dashboard', [AdminController::class, 'dashboard']);
        Route::get('users', [AdminController::class, 'getUsers']);
        Route::get('dropboxes', [AdminController::class, 'getDropboxes']);
        Route::get('topup-requests', [AdminController::class, 'getTopupRequests']);
        Route::get('transactions', [AdminController::class, 'getAllTransactions']);
    });
});

// Fallback jika endpoint tidak ditemukan
Route::fallback(function () {
    return response()->json([
        'success' => false,
        'message' => 'API endpoint not found',
    ], 404);
});
