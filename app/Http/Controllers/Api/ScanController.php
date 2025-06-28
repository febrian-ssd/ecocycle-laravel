<?php
// app/Http/Controllers/Api/ScanController.php - COMPLETE UPDATE
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Models\Dropbox;
use App\Models\History;
use App\Models\Transaction;
use App\Helpers\ApiResponse;

class ScanController extends Controller
{
    public function confirmScan(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'dropbox_code' => 'required|string',
            'waste_type' => 'required|string|in:plastic,paper,metal,glass,organic',
            'weight' => 'required|numeric|min:0.1|max:100',
        ]);

        if ($validator->fails()) {
            return ApiResponse::validationError($validator->errors());
        }

        try {
            $user = $request->user();

            $dropbox = Dropbox::where('id', $request->dropbox_code)
                             ->orWhere('location_name', 'LIKE', '%' . $request->dropbox_code . '%')
                             ->where('status', 'active')
                             ->first();

            if (!$dropbox) {
                return ApiResponse::error('Dropbox not found or under maintenance', 404);
            }

            $pointsPerGram = $this->getPointsPerGram($request->waste_type);
            $weightInGrams = $request->weight * 1000;
            $coins_awarded = (int)floor($weightInGrams * $pointsPerGram);

            DB::transaction(function () use ($user, $dropbox, $request, $coins_awarded, $weightInGrams) {
                $user->increment('balance_coins', $coins_awarded);

                History::create([
                    'user_id' => $user->id,
                    'dropbox_id' => $dropbox->id,
                    'waste_type' => $request->waste_type,
                    'weight' => $request->weight,
                    'coins_earned' => $coins_awarded,
                    'status' => 'success',
                    'scan_time' => now(),
                ]);

                Transaction::create([
                    'user_id' => $user->id,
                    'type' => 'scan_reward',
                    'amount_coins' => $coins_awarded,
                    'description' => "Scan {$request->waste_type} {$request->weight}kg - Reward {$coins_awarded} coins",
                ]);
            });

            return ApiResponse::success([
                'coins_earned' => $coins_awarded,
                'waste_type' => $request->waste_type,
                'weight' => $request->weight,
                'new_balance_coins' => $user->fresh()->balance_coins,
                'dropbox_location' => $dropbox->location_name,
            ], "Scan successful! You earned {$coins_awarded} coins!");

        } catch (\Exception $e) {
            return ApiResponse::error('Scan failed: ' . $e->getMessage(), 500);
        }
    }

    private function getPointsPerGram($wasteType)
    {
        $pointsMap = [
            'plastic' => 0.01,
            'paper' => 0.008,
            'metal' => 0.015,
            'glass' => 0.012,
            'organic' => 0.005,
        ];

        return $pointsMap[$wasteType] ?? 0.01;
    }
}
