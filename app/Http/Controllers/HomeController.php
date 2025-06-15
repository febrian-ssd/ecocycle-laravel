<?php

namespace App\Http\Controllers;

use App\Models\Dropbox;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        // Middleware 'auth' sudah dihapus dari sini agar semua orang bisa lihat peta.
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        // Logika untuk mengambil data dropbox dan menampilkan view peta
        $activeDropboxes = Dropbox::where('status', 'active')->get();
        return view('home', compact('activeDropboxes'));
    }
}
