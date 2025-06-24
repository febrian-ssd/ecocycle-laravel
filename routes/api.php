<?php
// routes/api.php - COMPLETE API ROUTES WITH FIXES

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DropboxController;
use App\Http\Controllers\Api\ScanController;
use App\Http\Controllers\Api\EcopayController;
use App\Http\Controllers\Api\HistoryController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Public routes (no authentication required)
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
});

// Alternative auth routes (for compatibility)
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Public dropbox routes
Route::get('/dropboxes', [DropboxController::class, 'index']);
Route::get('/dropboxes/{id}', [DropboxController::class, 'show']);
Route::post('/dropboxes/nearby', [DropboxController::class, 'getNearby']);
Route::get('/dropboxes/stats', [DropboxController::class, 'getStats']);

// Protected routes (require authentication)
Route::middleware('auth:sanctum')->group(function () {

    // Auth routes
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', function (Request $request) {
        return response()->json([
            'success' => true,
            'data' => $request->user()
        ]);
    });

    // User profile routes
    Route::get('/profile', [HistoryController::class, 'getUserProfile']);
    Route::put('/profile', function(Request $request) {
        try {
            $user = $request->user();
            $validated = $request->validate([
                'name' => 'sometimes|string|max:255',
                'email' => 'sometimes|email|unique:users,email,' . $user->id,
                'password' => 'sometimes|string|min:8',
            ]);

            if (isset($validated['password'])) {
                $validated['password'] = Hash::make($validated['password']);
            }

            $user->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Profile updated successfully',
                'user' => $user->fresh()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update profile: ' . $e->getMessage()
            ], 500);
        }
    });

    // Scan routes
    Route::prefix('scan')->group(function () {
        Route::post('/confirm', [ScanController::class, 'confirmScan']);
        Route::get('/history', [ScanController::class, 'getScanHistory']);
        Route::get('/stats', [ScanController::class, 'getScanStats']);
    });

    // History routes - FIXED: All missing endpoints
    Route::prefix('history')->group(function () {
        Route::get('/', [HistoryController::class, 'getHistory']); // General history
    });

    // CRITICAL: Missing history endpoints that Flutter is calling
    Route::get('/scan-history', [HistoryController::class, 'getScanHistory']);
    Route::get('/scan-stats', [HistoryController::class, 'getScanStats']);
    Route::get('/transaction-history', [HistoryController::class, 'getTransactionHistory']);
    Route::get('/history', [HistoryController::class, 'getHistory']); // Fallback

    // Wallet/EcoPay routes
    Route::prefix('wallet')->group(function () {
        Route::get('/', [EcopayController::class, 'getWallet']);
        Route::get('/balance', [EcopayController::class, 'getBalanceSummary']);
    });

    // CRITICAL: Wallet endpoint that Flutter is calling
    Route::get('/wallet', [EcopayController::class, 'getWallet']);

    // Transaction routes
    Route::prefix('transactions')->group(function () {
        Route::get('/', [EcopayController::class, 'getTransactions']);
        Route::post('/transfer', [EcopayController::class, 'transfer']);
        Route::post('/exchange-coins', [EcopayController::class, 'exchangeCoins']);
    });

    // CRITICAL: Transaction endpoints that Flutter is calling
    Route::get('/transactions', [EcopayController::class, 'getTransactions']);
    Route::post('/transfer', [EcopayController::class, 'transfer']);
    Route::post('/exchange-coins', [EcopayController::class, 'exchangeCoins']);

    // Top-up routes
    Route::prefix('topup')->group(function () {
        Route::post('/request', [EcopayController::class, 'createTopupRequest']);
        Route::get('/requests', [EcopayController::class, 'getTopupRequests']);
    });

    // CRITICAL: Topup endpoints that Flutter is calling
    Route::post('/topup-request', [EcopayController::class, 'createTopupRequest']);
    Route::get('/topup-requests', [EcopayController::class, 'getTopupRequests']);

    // User statistics
    Route::get('/stats', function(Request $request) {
        try {
            $user = $request->user();

            $totalScans = \App\Models\History::where('user_id', $user->id)
                                            ->where(function($query) {
                                                $query->where('status', 'success')->orWhereNull('status');
                                            })
                                            ->count();

            $totalWasteWeight = \App\Models\History::where('user_id', $user->id)->sum('weight');
            $totalCoinsEarned = \App\Models\History::where('user_id', $user->id)->sum('coins_earned');
            $totalTransactions = \App\Models\Transaction::where('user_id', $user->id)->count();

            return response()->json([
                'success' => true,
                'data' => [
                    'total_scans' => (int) $totalScans,
                    'total_waste_weight' => round((float) $totalWasteWeight, 2),
                    'total_coins_earned' => (int) $totalCoinsEarned,
                    'total_transactions' => (int) $totalTransactions,
                    'current_balance_rp' => (float) ($user->balance_rp ?? 0),
                    'current_balance_coins' => (int) ($user->balance_coins ?? 0),
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get stats',
                'data' => [
                    'total_scans' => 0,
                    'total_waste_weight' => 0,
                    'total_coins_earned' => 0,
                    'total_transactions' => 0,
                    'current_balance_rp' => 0,
                    'current_balance_coins' => 0,
                ]
            ], 500);
        }
    });

    // EMERGENCY: Fallback routes for debugging missing columns
    Route::get('/debug/tables', function() {
        try {
            $historyColumns = \Schema::getColumnListing('histories');
            $transactionColumns = \Schema::getColumnListing('transactions');
            $userColumns = \Schema::getColumnListing('users');

            return response()->json([
                'success' => true,
                'tables' => [
                    'histories' => $historyColumns,
                    'transactions' => $transactionColumns,
                    'users' => $userColumns,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    });
});

// Fallback route for API documentation or status
Route::get('/', function () {
    return response()->json([
        'message' => 'EcoCycle API',
        'version' => '1.0',
        'status' => 'active',
        'timestamp' => now()->toISOString(),
        'endpoints' => [
            'auth' => [
                'POST /api/auth/register',
                'POST /api/auth/login',
                'POST /api/register (alternative)',
                'POST /api/login (alternative)',
                'POST /api/logout (auth required)',
            ],
            'dropboxes' => [
                'GET /api/dropboxes',
                'GET /api/dropboxes/{id}',
                'POST /api/dropboxes/nearby',
                'GET /api/dropboxes/stats',
            ],
            'scan' => [
                'POST /api/scan/confirm (auth required)',
                'GET /api/scan/history (auth required)',
                'GET /api/scan/stats (auth required)',
            ],
            'history' => [
                'GET /api/history (auth required)',
                'GET /api/scan-history (auth required)',
                'GET /api/scan-stats (auth required)',
                'GET /api/transaction-history (auth required)',
            ],
            'wallet' => [
                'GET /api/wallet (auth required)',
                'GET /api/wallet/balance (auth required)',
            ],
            'transactions' => [
                'GET /api/transactions (auth required)',
                'POST /api/transfer (auth required)',
                'POST /api/exchange-coins (auth required)',
            ],
            'topup' => [
                'POST /api/topup-request (auth required)',
                'GET /api/topup-requests (auth required)',
            ],
            'profile' => [
                'GET /api/profile (auth required)',
                'PUT /api/profile (auth required)',
            ],
            'debug' => [
                'GET /api/debug/tables (auth required)',
            ],
        ]
    ]);
});

// Error handling for undefined routes
Route::fallback(function () {
    return response()->json([
        'success' => false,
        'message' => 'API endpoint not found',
        'error' => 'The requested endpoint does not exist',
        'suggestion' => 'Please check the API documentation at GET /api/',
        'timestamp' => now()->toISOString(),
    ], 404);
});
