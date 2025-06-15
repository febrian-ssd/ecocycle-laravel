<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\DropboxController;
use App\Http\Controllers\Admin\HistoryController;

// 1. Rute otentikasi (login, register, dll.)
Auth::routes();

// 2. Rute halaman utama kita, yang akan menampilkan peta
Route::get('/', [HomeController::class, 'index'])->name('home');

// 3. Hapus rute '/home' default dari Laravel karena kita tidak menggunakannya
// Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home'); // BARIS INI BIASANYA ADA DAN HARUS DIHAPUS/DIKOMENTARI

// 4. Grup rute khusus untuk admin
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::redirect('/', '/admin/users');
    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::get('/dropboxes', [DropboxController::class, 'index'])->name('dropboxes.index');
    Route::get('/history', [HistoryController::class, 'index'])->name('history.index');
    // ... rute admin lainnya ...
    Route::get('/dropboxes', [DropboxController::class, 'index'])->name('dropboxes.index');

// RUTE BARU: Menampilkan form tambah dropbox
    Route::get('/dropboxes/create', [DropboxController::class, 'create'])->name('dropboxes.create');

// RUTE BARU: Menyimpan data dari form
    Route::post('/dropboxes', [DropboxController::class, 'store'])->name('dropboxes.store');

    Route::get('/history', [HistoryController::class, 'index'])->name('history.index');
// ...
// RUTE BARU: Menampilkan form edit berdasarkan ID
    Route::get('/dropboxes/{dropbox}/edit', [DropboxController::class, 'edit'])->name('dropboxes.edit');

// RUTE BARU: Memproses update data dari form edit
    Route::put('/dropboxes/{dropbox}', [DropboxController::class, 'update'])->name('dropboxes.update');

    // RUTE BARU: Menampilkan form edit user
    Route::get('/users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
    // RUTE BARU: Memproses update data user
    Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');

});
// ... semua rute web Anda ...



