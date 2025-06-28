<?php
// app/Http/Controllers/Api/EcopayController.php - COMPLETE UPDATE
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Models\Transaction;
use App\Models\User;
use App\Models\TopupRequest;
use App\Helpers\ApiResponse;

class EcopayController extends Controller
{
    public function getWallet(Request $request)
    {
        try {
            $user = $request->user();
            return ApiResponse::success([
                'balance_rp' => (float) ($user->balance_rp ?? 0),
                'balance_koin' => (int) ($user->balance_coins ?? 0),
                'balance_coins' => (int) ($user->balance_coins ?? 0),
            ], 'Wallet data retrieved successfully');
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to get wallet data: ' . $e->getMessage(), 500);
        }
    }

    public function getAdminWallet(Request $request)
    {
        try {
            $user = $request->user();

            // Admin wallet overview with additional stats
            $totalUsers = User::where('role', 'user')->count();
            $totalBalance = User::sum('balance_rp');
            $totalCoins = User::sum('balance_coins');

            return ApiResponse::success([
                'admin_balance_rp' => (float) ($user->balance_rp ?? 0),
                'admin_balance_coins' => (int) ($user->balance_coins ?? 0),
                'total_users' => $totalUsers,
                'total_system_balance' => (float) $totalBalance,
                'total_system_coins' => (int) $totalCoins,
            ], 'Admin wallet overview retrieved successfully');
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to get admin wallet data: ' . $e->getMessage(), 500);
        }
    }

    public function createTopupRequest(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:10000|max:10000000',
            'payment_method' => 'nullable|string|max:100',
            'user_note' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return ApiResponse::validationError($validator->errors());
        }

        try {
            DB::beginTransaction();

            $topupRequest = TopupRequest::create([
                'user_id' => $request->user()->id,
                'amount' => $request->amount,
                'status' => 'pending',
                'type' => 'request',
                'payment_method' => $request->payment_method ?? 'transfer_bank',
                'user_note' => $request->user_note ?? '',
            ]);

            DB::commit();

            return ApiResponse::success([
                'request_id' => $topupRequest->id,
                'amount' => (float) $topupRequest->amount,
                'status' => $topupRequest->status,
                'created_at' => $topupRequest->created_at,
            ], 'Topup request created successfully', 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return ApiResponse::error('Failed to create topup request: ' . $e->getMessage(), 500);
        }
    }

    public function transfer(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|max:255',
            'amount' => 'required|numeric|min:1000',
            'description' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return ApiResponse::validationError($validator->errors());
        }

        try {
            $user = $request->user();
            $amount = (float) $request->amount;

            if (($user->balance_rp ?? 0) < $amount) {
                return ApiResponse::error('Insufficient balance', 422);
            }

            $recipient = User::where('email', $request->email)->first();
            if (!$recipient) {
                return ApiResponse::error('Recipient email not found', 404);
            }

            if ($recipient->id === $user->id) {
                return ApiResponse::error('Cannot transfer to yourself', 422);
            }

            DB::beginTransaction();

            $user->decrement('balance_rp', $amount);
            $recipient->increment('balance_rp', $amount);

            Transaction::create([
                'user_id' => $user->id,
                'type' => 'transfer_out',
                'amount_rp' => -$amount,
                'description' => "Transfer to {$recipient->name}"
            ]);

            Transaction::create([
                'user_id' => $recipient->id,
                'type' => 'transfer_in',
                'amount_rp' => $amount,
                'description' => "Transfer from {$user->name}"
            ]);

            DB::commit();

            return ApiResponse::success([
                'amount_transferred' => $amount,
                'recipient_name' => $recipient->name,
                'new_balance_rp' => (float) $user->fresh()->balance_rp,
            ], 'Transfer successful');

        } catch (\Exception $e) {
            DB::rollBack();
            return ApiResponse::error('Transfer failed: ' . $e->getMessage(), 500);
        }
    }

    public function exchangeCoins(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'coin_amount' => 'required|integer|min:100|max:50000',
        ]);

        if ($validator->fails()) {
            return ApiResponse::validationError($validator->errors());
        }

        try {
            $user = $request->user();
            $coinAmount = $request->coin_amount;
            $rupiahAmount = $coinAmount * 10;

            if ($user->balance_coins < $coinAmount) {
                return ApiResponse::error('Insufficient coins', 422);
            }

            DB::beginTransaction();

            $user->decrement('balance_coins', $coinAmount);
            $user->increment('balance_rp', $rupiahAmount);

            Transaction::create([
                'user_id' => $user->id,
                'type' => 'coin_exchange_to_rp',
                'amount_rp' => $rupiahAmount,
                'amount_coins' => -$coinAmount,
                'description' => "Exchange {$coinAmount} coins to Rp " . number_format($rupiahAmount, 0, ',', '.'),
            ]);

            DB::commit();

            return ApiResponse::success([
                'coins_exchanged' => $coinAmount,
                'rupiah_received' => $rupiahAmount,
                'new_balance_coins' => $user->fresh()->balance_coins,
                'new_balance_rp' => $user->fresh()->balance_rp,
            ], 'Coin exchange successful');

        } catch (\Exception $e) {
            DB::rollBack();
            return ApiResponse::error('Coin exchange failed: ' . $e->getMessage(), 500);
        }
    }

    public function getTransactions(Request $request)
    {
        try {
            $user = $request->user();
            $transactions = Transaction::where('user_id', $user->id)
                ->orderBy('created_at', 'desc')
                ->limit(50)
                ->get();

            return ApiResponse::success($transactions->map(function ($transaction) {
                return [
                    'id' => $transaction->id,
                    'type' => $transaction->type,
                    'amount_rp' => (float) ($transaction->amount_rp ?? 0),
                    'amount_coins' => (int) ($transaction->amount_coins ?? 0),
                    'description' => $transaction->description,
                    'created_at' => $transaction->created_at,
                ];
            }), 'Transactions retrieved successfully');

        } catch (\Exception $e) {
            return ApiResponse::error('Failed to get transactions: ' . $e->getMessage(), 500);
        }
    }

    public function getTopupRequests(Request $request)
    {
        try {
            $user = $request->user();
            $requests = TopupRequest::where('user_id', $user->id)
                ->orderBy('created_at', 'desc')
                ->limit(20)
                ->get()
                ->map(function ($request) {
                    return [
                        'id' => $request->id,
                        'amount' => (float) $request->amount,
                        'status' => $request->status,
                        'payment_method' => $request->payment_method,
                        'user_note' => $request->user_note,
                        'admin_note' => $request->admin_note,
                        'created_at' => $request->created_at,
                    ];
                });

            return ApiResponse::success($requests, 'Topup requests retrieved successfully');
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to get topup requests: ' . $e->getMessage(), 500);
        }
    }
}
