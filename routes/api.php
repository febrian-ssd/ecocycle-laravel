<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DropboxController;
use App\Http\Controllers\Api\EcopayController;
use App\Http\Controllers\Api\ScanController;
use App\Http\Controllers\Api\HistoryController;

// Health check endpoints
Route::get('/health', function () {
    return response()->json([
        'success' => true,
        'message' => 'EcoCycle API is running',
        'timestamp' => now(),
        'version' => '1.0.0',
        'laravel' => app()->version()
    ]);
});

Route::get('/', function () {
    return response()->json([
        'name' => 'EcoCycle API',
        'version' => '1.0.0',
        'status' => 'active',
        'endpoints' => [
            'health' => '/api/health',
            'login' => '/api/login',
            'register' => '/api/register',
        ]
    ]);
});

// Authentication routes (public)
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    // Auth endpoints
    Route::get('/auth/user', [AuthController::class, 'user']);
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::put('/user/profile', [AuthController::class, 'updateProfile']);
    Route::put('/admin/profile', [AuthController::class, 'updateAdminProfile']);

    // Dropbox endpoints
    Route::get('/dropboxes', [DropboxController::class, 'index']);
    Route::get('/dropboxes/{id}', [DropboxController::class, 'show']);
    Route::get('/dropboxes/nearby', [DropboxController::class, 'getNearby']);

    // User endpoints
    Route::prefix('user')->group(function () {
        Route::get('/wallet', [EcopayController::class, 'getWallet']);
        Route::post('/topup', [EcopayController::class, 'createTopupRequest']);
        Route::post('/transfer', [EcopayController::class, 'transfer']);
        Route::post('/exchange-coins', [EcopayController::class, 'exchangeCoins']);
        Route::get('/transactions', [EcopayController::class, 'getTransactions']);
        Route::get('/topup-requests', [EcopayController::class, 'getTopupRequests']);

        // Scan endpoints
        Route::post('/scan/confirm', [ScanController::class, 'confirmScan']);

        // History endpoints
        Route::get('/history', [HistoryController::class, 'getHistory']);
        Route::get('/scan-history', [HistoryController::class, 'getScanHistory']);
        Route::get('/scan-stats', [HistoryController::class, 'getScanStats']);
        Route::get('/transaction-history', [HistoryController::class, 'getTransactionHistory']);
        Route::get('/profile', [HistoryController::class, 'getUserProfile']);
    });

    // Admin endpoints
    Route::middleware('admin')->prefix('admin')->group(function () {
        Route::get('/wallet-overview', [EcopayController::class, 'getAdminWallet']);
    });
});
