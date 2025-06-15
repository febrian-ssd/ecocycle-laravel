<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController; // <-- PASTIKAN INI ADA
use App\Http\Controllers\Api\DropboxController;
use App\Http\Controllers\Api\EcopayController;
use App\Http\Controllers\Api\ScanController;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Rute publik untuk login, tidak perlu otentikasi
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Rute yang memerlukan otentikasi (token) untuk diakses
Route::middleware('auth:sanctum')->group(function () {
    // Rute untuk mendapatkan data user yang sedang login
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // Rute untuk logout
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/dropboxes', [DropboxController::class, 'index']);

    // RUTE BARU UNTUK ECOPAY
    Route::get('/wallet', [EcopayController::class, 'getWallet']);
    Route::get('/transactions', [EcopayController::class, 'getTransactions']);
    Route::post('/scans/confirm', [ScanController::class, 'confirmScan']);

});
