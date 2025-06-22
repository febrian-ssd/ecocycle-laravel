<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\DropboxController;
use App\Http\Controllers\Admin\HistoryController;
use App\Http\Controllers\Admin\SaldoController;

/*
|--------------------------------------------------------------------------
| Authentication Routes
|--------------------------------------------------------------------------
*/
Auth::routes();

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/
Route::get('/', [HomeController::class, 'index'])->name('home');

/*
|--------------------------------------------------------------------------
| Protected Routes - Authenticated Users
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->group(function () {
    // User home page
    Route::get('/home', [HomeController::class, 'index'])->name('home');
});

/*
|--------------------------------------------------------------------------
| Admin Routes - Admin Only
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {

    // Admin Dashboard Redirect
    Route::redirect('/', '/admin/users');

    // User Management Routes
    Route::prefix('users')->name('users.')->group(function () {
        Route::get('/', [UserController::class, 'index'])->name('index');
        Route::get('/{user}/edit', [UserController::class, 'edit'])->name('edit');
        Route::put('/{user}', [UserController::class, 'update'])->name('update');
        Route::delete('/{user}', [UserController::class, 'destroy'])->name('destroy');
    });

    // Dropbox Management Routes
    Route::prefix('dropboxes')->name('dropboxes.')->group(function () {
        Route::get('/', [DropboxController::class, 'index'])->name('index');
        Route::get('/create', [DropboxController::class, 'create'])->name('create');
        Route::post('/', [DropboxController::class, 'store'])->name('store');
        Route::get('/{dropbox}/edit', [DropboxController::class, 'edit'])->name('edit');
        Route::put('/{dropbox}', [DropboxController::class, 'update'])->name('update');
        Route::delete('/{dropbox}', [DropboxController::class, 'destroy'])->name('destroy'); // <- ROUTE YANG HILANG
        Route::get('/{dropbox}', [DropboxController::class, 'show'])->name('show'); // Optional: untuk view detail
    });

    // History Management Routes
    Route::prefix('history')->name('history.')->group(function () {
        Route::get('/', [HistoryController::class, 'index'])->name('index');
        Route::get('/{history}', [HistoryController::class, 'show'])->name('show'); // Optional: untuk view detail
        Route::delete('/{history}', [HistoryController::class, 'destroy'])->name('destroy'); // Optional: untuk delete history
    });

    // Saldo Management Routes
    Route::prefix('saldo')->name('saldo.')->group(function () {
        Route::prefix('topup')->name('topup.')->group(function () {
            Route::get('/', [SaldoController::class, 'topupIndex'])->name('index');
            Route::post('/{topupRequest}/approve', [SaldoController::class, 'approveTopup'])->name('approve');
        });
    });
});
