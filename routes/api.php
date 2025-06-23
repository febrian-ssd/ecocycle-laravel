<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DropboxController;
use App\Http\Controllers\Api\EcopayController;
use App\Http\Controllers\Api\ScanController;
use App\Http\Controllers\Api\HistoryController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Rute publik
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Rute yang memerlukan otentikasi (wajib login)
Route::middleware('auth:sanctum')->group(function () {

    // === AUTH & USER ROUTES ===
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/profile', [HistoryController::class, 'getUserProfile']);

    // === DROPBOX & MAP ROUTES ===
    Route::get('/dropboxes', [DropboxController::class, 'index']);
    Route::get('/dropboxes/{id}', [DropboxController::class, 'show']);
    Route::get('/dropboxes/nearby', [DropboxController::class, 'getNearby']);
    Route::get('/dropboxes/stats', [DropboxController::class, 'getStats']);

    // === ECOPAY & WALLET ROUTES ===
    Route::get('/wallet', [EcopayController::class, 'getWallet']);
    Route::get('/balance/summary', [EcopayController::class, 'getBalanceSummary']);

    // === TRANSACTION ROUTES ===
    Route::get('/transactions', [EcopayController::class, 'getTransactions']);
    Route::get('/transactions/history', [HistoryController::class, 'getTransactionHistory']);
    Route::post('/transfer', [EcopayController::class, 'transfer']);
    Route::post('/exchange-coins', [EcopayController::class, 'exchangeCoins']);

    // === TOPUP ROUTES ===
    Route::post('/topup-request', [EcopayController::class, 'createTopupRequest']);
    Route::get('/topup-requests', [EcopayController::class, 'getTopupRequests']);

    // === SCAN & HISTORY ROUTES ===
    Route::post('/scan/confirm', [ScanController::class, 'confirmScan']);
    Route::get('/history', [HistoryController::class, 'getScanHistory']);
    Route::get('/history/stats', [HistoryController::class, 'getScanStats']);

    // === LEGACY SUPPORT (untuk backward compatibility) ===
    Route::post('/scans/confirm', [ScanController::class, 'confirmScan']); // old endpoint
    Route::post('/coins/exchange', [EcopayController::class, 'exchangeCoins']); // old endpoint

});

// === FALLBACK ROUTES ===
Route::fallback(function(){
    return response()->json([
        'message' => 'API endpoint not found. Please check the documentation.',
        'available_endpoints' => [
            'POST /api/login',
            'POST /api/register',
            'GET /api/user',
            'GET /api/profile',
            'GET /api/dropboxes',
            'GET /api/wallet',
            'GET /api/transactions',
            'GET /api/history',
            'POST /api/transfer',
            'POST /api/exchange-coins',
            'POST /api/scan/confirm',
            'POST /api/topup-request'
        ]
    ], 404);
});
