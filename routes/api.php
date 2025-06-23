<?php
// routes/api.php - COMPLETE API ROUTES

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DropboxController;
use App\Http\Controllers\Api\ScanController;
use App\Http\Controllers\Api\EcopayController;
use App\Http\Controllers\Api\HistoryController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Public routes (no authentication required)
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
});

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
        return $request->user();
    });

    // User profile routes
    Route::get('/profile', [HistoryController::class, 'getUserProfile']);
    Route::put('/profile', function(Request $request) {
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
        return response()->json(['message' => 'Profile updated successfully', 'user' => $user->fresh()]);
    });

    // Scan routes
    Route::prefix('scan')->group(function () {
        Route::post('/confirm', [ScanController::class, 'confirmScan']);
        Route::get('/history', [ScanController::class, 'getScanHistory']);
        Route::get('/stats', [ScanController::class, 'getScanStats']);
    });

    // History routes - NEW ROUTES FOR MISSING ENDPOINTS
    Route::prefix('history')->group(function () {
        Route::get('/', [HistoryController::class, 'getScanHistory']); // General history
    });

    // NEW: Specific history endpoints
    Route::get('/scan-history', [HistoryController::class, 'getScanHistory']);
    Route::get('/scan-stats', [HistoryController::class, 'getScanStats']);
    Route::get('/transaction-history', [HistoryController::class, 'getTransactionHistory']);

    // Wallet/EcoPay routes
    Route::prefix('wallet')->group(function () {
        Route::get('/', [EcopayController::class, 'getWallet']);
        Route::get('/balance', [EcopayController::class, 'getBalanceSummary']);
    });

    // Transaction routes
    Route::prefix('transactions')->group(function () {
        Route::get('/', [EcopayController::class, 'getTransactions']);
        Route::post('/transfer', [EcopayController::class, 'transfer']);
        Route::post('/exchange-coins', [EcopayController::class, 'exchangeCoins']);
    });

    // Alternative endpoints for consistency
    Route::get('/transactions', [EcopayController::class, 'getTransactions']);
    Route::post('/transfer', [EcopayController::class, 'transfer']);
    Route::post('/exchange-coins', [EcopayController::class, 'exchangeCoins']);

    // Top-up routes
    Route::prefix('topup')->group(function () {
        Route::post('/request', [EcopayController::class, 'createTopupRequest']);
        Route::get('/requests', [EcopayController::class, 'getTopupRequests']);
    });

    // Alternative topup routes
    Route::post('/topup-request', [EcopayController::class, 'createTopupRequest']);
    Route::get('/topup-requests', [EcopayController::class, 'getTopupRequests']);

    // User statistics
    Route::get('/stats', function(Request $request) {
        $user = $request->user();

        $totalScans = \App\Models\History::where('user_id', $user->id)->where('status', 'success')->count();
        $totalWasteWeight = \App\Models\History::where('user_id', $user->id)->sum('weight');
        $totalCoinsEarned = \App\Models\History::where('user_id', $user->id)->sum('coins_earned');
        $totalTransactions = \App\Models\Transaction::where('user_id', $user->id)->count();

        return response()->json([
            'success' => true,
            'data' => [
                'total_scans' => $totalScans,
                'total_waste_weight' => round((float) $totalWasteWeight, 2),
                'total_coins_earned' => $totalCoinsEarned,
                'total_transactions' => $totalTransactions,
                'current_balance_rp' => $user->balance_rp ?? 0,
                'current_balance_coins' => $user->balance_coins ?? 0,
            ]
        ]);
    });
});

// Fallback route for API documentation or status
Route::get('/', function () {
    return response()->json([
        'message' => 'EcoCycle API',
        'version' => '1.0',
        'status' => 'active',
        'endpoints' => [
            'auth' => [
                'POST /api/auth/register',
                'POST /api/auth/login',
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
        ]
    ]);
});

// Error handling for undefined routes
Route::fallback(function () {
    return response()->json([
        'message' => 'API endpoint not found',
        'error' => 'The requested endpoint does not exist',
        'suggestion' => 'Please check the API documentation at GET /api/'
    ], 404);
});
