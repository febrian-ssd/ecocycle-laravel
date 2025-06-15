<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\History; // <-- Import model History

class HistoryController extends Controller
{
    /**
     * Menampilkan halaman riwayat scan.
     */
    public function index()
    {
        // Ambil semua data riwayat dari yang paling baru.
        // 'with' digunakan untuk mengambil data relasi (user & dropbox)
        // agar lebih efisien dan cepat (mengatasi N+1 problem).
        $histories = History::with(['user', 'dropbox'])->latest()->get();

        return view('admin.history.index', compact('histories'));
    }
}
