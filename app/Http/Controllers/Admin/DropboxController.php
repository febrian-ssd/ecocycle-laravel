<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Dropbox;
use Illuminate\Support\Facades\DB;

class DropboxController extends Controller
{
    /**
     * Menampilkan halaman daftar dropbox beserta statistik.
     */
    public function index()
    {
        // Ambil semua data dari database
        $dropboxes = Dropbox::orderBy('created_at', 'desc')->get();

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
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'status' => 'required|in:active,maintenance',
        ]);

        try {
            DB::beginTransaction();

            Dropbox::create([
                'location_name' => $request->location_name,
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'status' => $request->status,
            ]);

            DB::commit();

            return redirect()->route('admin.dropboxes.index')
                           ->with('success', 'Lokasi dropbox baru berhasil ditambahkan!');

        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()
                           ->withInput()
                           ->with('error', 'Gagal menambahkan dropbox: ' . $e->getMessage());
        }
    }

    /**
     * Menampilkan detail dropbox.
     */
    public function show(Dropbox $dropbox)
    {
        return view('admin.dropboxes.show', compact('dropbox'));
    }

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
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'status' => 'required|in:active,maintenance',
        ]);

        try {
            DB::beginTransaction();

            $dropbox->update([
                'location_name' => $request->location_name,
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'status' => $request->status,
            ]);

            DB::commit();

            return redirect()->route('admin.dropboxes.index')
                           ->with('success', 'Data dropbox berhasil diperbarui!');

        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()
                           ->withInput()
                           ->with('error', 'Gagal memperbarui dropbox: ' . $e->getMessage());
        }
    }

    /**
     * Menghapus dropbox dari database.
     */
    public function destroy(Dropbox $dropbox)
    {
        try {
            DB::beginTransaction();

            $locationName = $dropbox->location_name;
            $dropbox->delete();

            DB::commit();

            return redirect()->route('admin.dropboxes.index')
                           ->with('success', "Dropbox {$locationName} berhasil dihapus!");

        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->route('admin.dropboxes.index')
                           ->with('error', 'Gagal menghapus dropbox: ' . $e->getMessage());
        }
    }
}
