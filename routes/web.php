<?php
// routes/web.php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\DropboxController;
use App\Http\Controllers\Admin\HistoryController;
use App\Http\Controllers\Admin\SaldoController;

// Public routes
Route::get('/', [HomeController::class, 'index'])->name('home');

// Authentication routes (jika menggunakan Laravel UI)
Auth::routes();

// Admin routes - protected by auth and admin middleware
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {

    // Dashboard
    Route::get('/dashboard', function () {
        return view('admin.dashboard');
    })->name('dashboard');

    // User Management Routes
    Route::resource('users', UserController::class)->names([
        'index' => 'users.index',
        'create' => 'users.create',
        'store' => 'users.store',
        'show' => 'users.show',
        'edit' => 'users.edit',
        'update' => 'users.update',
        'destroy' => 'users.destroy'
    ]);

    // Dropbox Management Routes
    Route::resource('dropboxes', DropboxController::class)->names([
        'index' => 'dropboxes.index',
        'create' => 'dropboxes.create',
        'store' => 'dropboxes.store',
        'show' => 'dropboxes.show',
        'edit' => 'dropboxes.edit',
        'update' => 'dropboxes.update',
        'destroy' => 'dropboxes.destroy'
    ]);

    // History Routes
    Route::prefix('history')->name('history.')->group(function () {
        Route::get('/', [HistoryController::class, 'index'])->name('index');
        Route::get('/{history}', [HistoryController::class, 'show'])->name('show');
        Route::delete('/{history}', [HistoryController::class, 'destroy'])->name('destroy');
        Route::get('/export/csv', [HistoryController::class, 'export'])->name('export');
        Route::get('/stats/data', [HistoryController::class, 'getStats'])->name('stats');
        Route::get('/chart/data', [HistoryController::class, 'getChartData'])->name('chart');
    });

    // Saldo/Topup Management Routes
    Route::prefix('saldo')->name('saldo.')->group(function () {
        Route::get('/topup', [SaldoController::class, 'topupIndex'])->name('topup.index');
        Route::get('/topup/{topupRequest}', [SaldoController::class, 'show'])->name('topup.show');
        Route::put('/topup/{topupRequest}/approve', [SaldoController::class, 'approveTopup'])->name('topup.approve');
        Route::put('/topup/{topupRequest}/reject', [SaldoController::class, 'rejectTopup'])->name('topup.reject');
        Route::post('/topup/manual', [SaldoController::class, 'manualTopup'])->name('topup.manual');
    });
});
