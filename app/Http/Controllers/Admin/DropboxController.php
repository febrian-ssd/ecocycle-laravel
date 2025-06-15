<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Dropbox;

class DropboxController extends Controller
{
    /**
     * Menampilkan halaman daftar dropbox beserta statistik.
     */
    public function index()
    {
        // Ambil semua data dari database
        $dropboxes = Dropbox::all();

        // Hitung statistik untuk kartu
        $totalDropboxes = $dropboxes->count();
        $activeDropboxes = $dropboxes->where('status', 'active')->count();
        $maintenanceDropboxes = $dropboxes->where('status', 'maintenance')->count();

        // Kirim semua data ke view
        return view('admin.dropboxes.index', compact('dropboxes', 'totalDropboxes', 'activeDropboxes', 'maintenanceDropboxes'));
    }

    /**
     * Menampilkan form untuk membuat dropbox baru.
     */
    public function create()
    {
        return view('admin.dropboxes.create');
    }

    /**
     * Menyimpan dropbox baru ke database.
     */
    public function store(Request $request)
    {
        $request->validate([
            'location_name' => 'required|string|max:255',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'status' => 'required|in:active,maintenance',
        ]);

        Dropbox::create($request->all());

        return redirect()->route('admin.dropboxes.index')
                         ->with('success', 'Lokasi dropbox baru berhasil ditambahkan!');
    }

    // Nanti kita akan tambahkan method edit dan update di sini
    // ... Method store() ...

    /**
     * Menampilkan form untuk mengedit dropbox.
     */
    public function edit(Dropbox $dropbox)
    {
        return view('admin.dropboxes.edit', compact('dropbox'));
    }

    /**
     * Mengupdate data dropbox di database.
     */
    public function update(Request $request, Dropbox $dropbox)
    {
        $request->validate([
            'location_name' => 'required|string|max:255',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'status' => 'required|in:active,maintenance',
        ]);

        $dropbox->update($request->all());

        return redirect()->route('admin.dropboxes.index')
                         ->with('success', 'Data dropbox berhasil diupdate!');
    }
}

