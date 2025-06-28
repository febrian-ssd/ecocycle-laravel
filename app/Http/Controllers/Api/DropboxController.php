<?php
// app/Http/Controllers/Api/DropboxController.php - COMPLETE UPDATE
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Dropbox;
use App\Helpers\ApiResponse;

class DropboxController extends Controller
{
    public function index()
    {
        try {
            $dropboxes = Dropbox::where('status', 'active')
                               ->orderBy('location_name')
                               ->get();

            return ApiResponse::success($dropboxes, 'Dropboxes retrieved successfully');

        } catch (\Exception $e) {
            return ApiResponse::error('Failed to get dropboxes: ' . $e->getMessage(), 500);
        }
    }

    public function show($id)
    {
        try {
            $dropbox = Dropbox::findOrFail($id);
            return ApiResponse::success($dropbox, 'Dropbox retrieved successfully');
        } catch (\Exception $e) {
            return ApiResponse::error('Dropbox not found', 404);
        }
    }

    public function getNearby(Request $request)
    {
        $validated = $request->validate([
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'radius' => 'nullable|numeric|min:0.1|max:50',
        ]);

        try {
            $userLat = $validated['latitude'];
            $userLng = $validated['longitude'];
            $radius = $validated['radius'] ?? 10;

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

            return ApiResponse::success($dropboxes, 'Nearby dropboxes retrieved successfully');

        } catch (\Exception $e) {
            return ApiResponse::error('Failed to find nearby dropboxes: ' . $e->getMessage(), 500);
        }
    }
}
