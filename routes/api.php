<?php
// routes/api.php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DropboxController;
use App\Http\Controllers\Api\EcopayController;
use App\Http\Controllers\Api\ScanController;
use App\Http\Controllers\Api\HistoryController;

// Test endpoint
Route::get('/', function () {
    return response()->json([
        'message' => 'EcoCycle API',
        'version' => '1.0.0',
        'status' => 'running'
    ]);
});

Route::get('/health', function () {
    return response()->json([
        'success' => true,
        'message' => 'API is healthy',
        'timestamp' => now()
    ]);
});

// Authentication
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/auth/user', [AuthController::class, 'user']);
    Route::post('/auth/logout', [AuthController::class, 'logout']);

    Route::get('/dropboxes', [DropboxController::class, 'index']);

    Route::prefix('user')->group(function () {
        Route::get('/wallet', [EcopayController::class, 'getWallet']);
        Route::get('/transactions', [EcopayController::class, 'getTransactions']);
        Route::post('/transfer', [EcopayController::class, 'transfer']);
        Route::post('/topup', [EcopayController::class, 'createTopupRequest']);
        Route::post('/exchange-coins', [EcopayController::class, 'exchangeCoins']);
        Route::post('/scan/confirm', [ScanController::class, 'confirmScan']);
        Route::get('/history', [HistoryController::class, 'getHistory']);
    });

    Route::middleware('admin')->prefix('admin')->group(function () {
        Route::get('/wallet-overview', [EcopayController::class, 'getAdminWallet']);
    });
});
