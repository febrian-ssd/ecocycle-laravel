<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DropboxController;
use App\Http\Controllers\Api\EcopayController;
use App\Http\Controllers\Api\ScanController;
use App\Models\TopupRequest; // <-- Import model baru

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
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    Route::post('/logout', [AuthController::class, 'logout']);

    // Rute untuk Peta
    Route::get('/dropboxes', [DropboxController::class, 'index']);

    // Rute untuk EcoPay
    Route::get('/wallet', [EcopayController::class, 'getWallet']);
    Route::get('/transactions', [EcopayController::class, 'getTransactions']);
    Route::post('/transfer', [EcopayController::class, 'transfer']);
    Route::post('/coins/exchange', [EcopayController::class, 'exchangeCoins']);
    
    // Rute untuk Scan
    Route::post('/scans/confirm', [ScanController::class, 'confirmScan']);

    // === RUTE BARU UNTUK PERMINTAAN ISI SALDO DARI FLUTTER ===
    Route::post('/topup-request', function (Request $request) {
        $validated = $request->validate(['amount' => 'required|integer|min:10000']);
        
        // Buat record baru di tabel topup_requests dengan status 'pending'
        $request->user()->topupRequests()->create([
            'amount' => $validated['amount'],
            'status' => 'pending',
        ]);

        return response()->json(['message' => 'Permintaan top up Anda telah dikirim dan sedang diproses.']);
    })->name('api.topup.request');

});