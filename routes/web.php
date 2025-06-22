<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\DropboxController;
use App\Http\Controllers\Admin\HistoryController;
use App\Http\Controllers\Admin\SaldoController;

Auth::routes();

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::redirect('/', '/admin/users');

    // User Management
    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::get('/users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
    Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
    Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy'); // <-- RUTE BARU

    // Dropbox Management
    Route::get('/dropboxes', [DropboxController::class, 'index'])->name('dropboxes.index');
    Route::get('/dropboxes/create', [DropboxController::class, 'create'])->name('dropboxes.create');
    Route::post('/dropboxes', [DropboxController::class, 'store'])->name('dropboxes.store');
    Route::get('/dropboxes/{dropbox}/edit', [DropboxController::class, 'edit'])->name('dropboxes.edit');
    Route::put('/dropboxes/{dropbox}', [DropboxController::class, 'update'])->name('dropboxes.update');

    // History
    Route::get('/history', [HistoryController::class, 'index'])->name('history.index');

    // Saldo Management
    Route::get('/saldo/topup', [SaldoController::class, 'topupIndex'])->name('saldo.topup.index');
    Route::post('/saldo/topup/{topupRequest}/approve', [SaldoController::class, 'approveTopup'])->name('saldo.topup.approve');
    Route::delete('/admin/dropboxes/{dropbox}', [DropboxController::class, 'destroy'])->name('admin.dropboxes.destroy');
});
