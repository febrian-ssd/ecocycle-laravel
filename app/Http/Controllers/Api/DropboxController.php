<?php
// app/Http/Controllers/Api/DropboxController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Dropbox;

class DropboxController extends Controller
{
    /**
     * Mengembalikan daftar semua dropbox yang aktif.
     */
    public function index()
    {
        try {
            $dropboxes = Dropbox::where('status', 'active')
                               ->orderBy('location_name')
                               ->get();

            // Return sebagai array langsung untuk konsistensi parsing
            return response()->json($dropboxes);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Gagal mengambil data dropbox',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get dropbox details by ID
     */
    public function show($id)
    {
        try {
            $dropbox = Dropbox::findOrFail($id);

            return response()->json([
                'data' => $dropbox
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Dropbox tidak ditemukan',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Get nearby dropboxes based on user location
     */
    public function getNearby(Request $request)
    {
        $validated = $request->validate([
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'radius' => 'nullable|numeric|min:0.1|max:50', // radius in km
        ]);

        $userLat = $validated['latitude'];
        $userLng = $validated['longitude'];
        $radius = $validated['radius'] ?? 10; // default 10km

        try {
            // Menggunakan Haversine formula untuk mencari dropbox terdekat
            $dropboxes = Dropbox::where('status', 'active')
                ->selectRaw("
                    *,
                    (6371 * acos(
                        cos(radians(?)) *
                        cos(radians(latitude)) *
                        cos(radians(longitude) - radians(?)) +
                        sin(radians(?)) *
                        sin(radians(latitude))
                    )) AS distance
                ", [$userLat, $userLng, $userLat])
                ->having('distance', '<=', $radius)
                ->orderBy('distance')
                ->get();

            return response()->json($dropboxes);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Gagal mencari dropbox terdekat',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get dropbox statistics
     */
    public function getStats()
    {
        try {
            $stats = [
                'total_dropboxes' => Dropbox::count(),
                'active_dropboxes' => Dropbox::where('status', 'active')->count(),
                'maintenance_dropboxes' => Dropbox::where('status', 'maintenance')->count(),
                'locations' => Dropbox::where('status', 'active')
                                     ->select('location_name', 'latitude', 'longitude')
                                     ->get()
            ];

            return response()->json($stats);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Gagal mengambil statistik dropbox',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
