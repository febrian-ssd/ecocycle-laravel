<?php
// routes/api.php - COMPLETE UPDATE
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DropboxController;
use App\Http\Controllers\Api\ScanController;
use App\Http\Controllers\Api\EcopayController;
use App\Http\Controllers\Api\HistoryController;
use App\Http\Controllers\Api\AdminController;

// ========================================================================
// HEALTH CHECK ROUTE
// ========================================================================
Route::get('health', function () {
    return response()->json([
        'success' => true,
        'status' => 'ok',
        'timestamp' => now(),
        'version' => '1.0.0'
    ]);
});

// ========================================================================
// PUBLIC ROUTES
// ========================================================================
Route::post('login', [AuthController::class, 'login']);
Route::post('register', [AuthController::class, 'register']);
Route::get('dropboxes', [DropboxController::class, 'index']);
Route::get('dropboxes/nearby', [DropboxController::class, 'getNearby']);
Route::get('dropboxes/{id}', [DropboxController::class, 'show']);

// ========================================================================
// PROTECTED ROUTES
// ========================================================================
Route::middleware(['auth:sanctum'])->group(function () {

    // --- Authentication Routes ---
    Route::prefix('auth')->group(function () {
        Route::get('user', [AuthController::class, 'user']);
        Route::post('logout', [AuthController::class, 'logout']);
        Route::post('logout-all', [AuthController::class, 'logoutAll']);
        Route::get('check-token', [AuthController::class, 'checkToken']);
    });

    // --- User Routes ---
    Route::middleware(['role:user'])->prefix('user')->group(function () {
        // Profile
        Route::get('profile', [HistoryController::class, 'getUserProfile']);
        Route::put('profile', [AuthController::class, 'updateProfile']);

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

    // --- Admin Routes ---
    Route::middleware(['role:admin'])->prefix('admin')->group(function () {
        Route::get('dashboard', [AdminController::class, 'dashboard']);
        Route::get('wallet-overview', [EcopayController::class, 'getAdminWallet']);
        Route::get('users', [AdminController::class, 'getUsers']);
        Route::get('dropboxes', [AdminController::class, 'getDropboxes']);
        Route::get('topup-requests', [AdminController::class, 'getTopupRequests']);
        Route::get('transactions', [AdminController::class, 'getAllTransactions']);
        Route::put('profile', [AuthController::class, 'updateAdminProfile']);
    });
});

// Fallback
Route::fallback(function () {
    return response()->json([
        'success' => false,
        'message' => 'API endpoint not found',
    ], 404);
});
