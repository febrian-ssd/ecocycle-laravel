<?php
// routes/api.php - Enhanced with Role-based Routing

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DropboxController;
use App\Http\Controllers\Api\ScanController;
use App\Http\Controllers\Api\EcopayController;
use App\Http\Controllers\Api\HistoryController;
use App\Http\Controllers\Api\AdminController;

/*
|--------------------------------------------------------------------------
| API Routes with Role-based Access Control
|--------------------------------------------------------------------------
*/

// Public routes (no authentication required)
Route::prefix('auth')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
});

// Alternative auth routes for compatibility
Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);

// Public dropbox routes (for map display)
Route::get('dropboxes', [DropboxController::class, 'index']);
Route::get('dropboxes/{id}', [DropboxController::class, 'show']);
Route::post('dropboxes/nearby', [DropboxController::class, 'getNearby']);

// Protected routes (require authentication)
Route::middleware(['auth:sanctum'])->group(function () {

    // General authenticated routes (all roles)
    Route::post('auth/logout', [AuthController::class, 'logout']);
    Route::post('auth/logout-all', [AuthController::class, 'logoutAll']);
    Route::get('auth/user', [AuthController::class, 'user']);
    Route::get('auth/check-token', [AuthController::class, 'checkToken']);

    // User-only routes (normal users, not admins)
    Route::middleware(['role:user'])->prefix('user')->group(function () {

        // User profile management
        Route::get('profile', [HistoryController::class, 'getUserProfile']);
        Route::put('profile', function(Request $request) {
            // Profile update logic for users
            try {
                $user = $request->user();
                $validated = $request->validate([
                    'name' => 'sometimes|string|max:255',
                    'password' => 'sometimes|string|min:6|confirmed',
                ]);

                if (isset($validated['password'])) {
                    $validated['password'] = Hash::make($validated['password']);
                }

                $user->update($validated);

                return response()->json([
                    'success' => true,
                    'message' => 'Profile updated successfully',
                    'data' => ['user' => $user->fresh()]
                ]);

            } catch (\Exception $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to update profile',
                    'error' => $e->getMessage()
                ], 500);
            }
        });

        // Wallet and payment routes
        Route::prefix('wallet')->group(function () {
            Route::get('/', [EcopayController::class, 'getWallet']);
            Route::get('balance', [EcopayController::class, 'getWallet']);
        });

        Route::prefix('transactions')->group(function () {
            Route::get('/', [EcopayController::class, 'getTransactions']);
            Route::post('transfer', [EcopayController::class, 'transfer']);
            Route::post('exchange-coins', [EcopayController::class, 'exchangeCoins']);
        });

        // Top-up requests
        Route::prefix('topup')->group(function () {
            Route::post('request', [EcopayController::class, 'createTopupRequest']);
            Route::get('requests', [EcopayController::class, 'getTopupRequests']);
        });

        // Scan and waste management
        Route::prefix('scan')->group(function () {
            Route::post('confirm', [ScanController::class, 'confirmScan']);
            Route::get('history', [ScanController::class, 'getScanHistory']);
            Route::get('stats', [ScanController::class, 'getScanStats']);
        });

        // History and statistics
        Route::prefix('history')->group(function () {
            Route::get('/', [HistoryController::class, 'getHistory']);
            Route::get('scans', [HistoryController::class, 'getScanHistory']);
            Route::get('transactions', [HistoryController::class, 'getTransactionHistory']);
            Route::get('stats', [HistoryController::class, 'getScanStats']);
        });

        // Dropbox information for users
        Route::get('dropboxes', [DropboxController::class, 'index']);
        Route::get('dropboxes/stats', [DropboxController::class, 'getStats']);
    });

    // Admin-only routes
    Route::middleware(['role:admin'])->prefix('admin')->group(function () {

        // Dashboard and statistics
        Route::get('dashboard', [AdminController::class, 'dashboard']);
        Route::get('stats', [AdminController::class, 'getSystemStats']);

        // User management
        Route::prefix('users')->group(function () {
            Route::get('/', [AdminController::class, 'getUsers']);
            Route::get('{id}', [AdminController::class, 'getUser']);
            Route::put('{id}', [AdminController::class, 'updateUser']);
            Route::delete('{id}', [AdminController::class, 'deleteUser']);
            Route::post('{id}/activate', [AdminController::class, 'activateUser']);
            Route::post('{id}/deactivate', [AdminController::class, 'deactivateUser']);
            Route::post('{id}/change-role', [AdminController::class, 'changeUserRole']);
        });

        // Dropbox management
        Route::prefix('dropboxes')->group(function () {
            Route::get('/', [AdminController::class, 'getDropboxes']);
            Route::post('/', [AdminController::class, 'createDropbox']);
            Route::get('{id}', [AdminController::class, 'getDropbox']);
            Route::put('{id}', [AdminController::class, 'updateDropbox']);
            Route::delete('{id}', [AdminController::class, 'deleteDropbox']);
        });

        // Top-up request management
        Route::prefix('topup-requests')->group(function () {
            Route::get('/', [AdminController::class, 'getTopupRequests']);
            Route::get('{id}', [AdminController::class, 'getTopupRequest']);
            Route::post('{id}/approve', [AdminController::class, 'approveTopupRequest']);
            Route::post('{id}/reject', [AdminController::class, 'rejectTopupRequest']);
        });

        // Transaction monitoring
        Route::prefix('transactions')->group(function () {
            Route::get('/', [AdminController::class, 'getAllTransactions']);
            Route::get('stats', [AdminController::class, 'getTransactionStats']);
            Route::get('export', [AdminController::class, 'exportTransactions']);
        });

        // History and monitoring
        Route::prefix('history')->group(function () {
            Route::get('/', [AdminController::class, 'getAllHistory']);
            Route::get('scans', [AdminController::class, 'getAllScanHistory']);
            Route::get('export', [AdminController::class, 'exportHistory']);
        });

        // System management
        Route::prefix('system')->group(function () {
            Route::get('health', [AdminController::class, 'systemHealth']);
            Route::get('logs', [AdminController::class, 'getSystemLogs']);
            Route::post('backup', [AdminController::class, 'createBackup']);
        });
    });

    // Common routes for both admin and user (with different data scope)
    Route::get('wallet', function(Request $request) {
        $user = $request->user();
        if ($user->isAdmin()) {
            // Admin gets aggregated wallet data
            return app(AdminController::class)->getWalletOverview($request);
        } else {
            // User gets personal wallet data
            return app(EcopayController::class)->getWallet($request);
        }
    });

    Route::get('stats', function(Request $request) {
        $user = $request->user();
        if ($user->isAdmin()) {
            // Admin gets system-wide stats
            return app(AdminController::class)->getSystemStats($request);
        } else {
            // User gets personal stats
            return app(HistoryController::class)->getScanStats($request);
        }
    });
});

// Fallback route for API documentation
Route::get('/', function () {
    return response()->json([
        'message' => 'EcoCycle API v2.0',
        'status' => 'active',
        'timestamp' => now()->toISOString(),
        'features' => [
            'role_based_access_control' => true,
            'token_based_authentication' => true,
            'admin_panel_support' => true,
            'user_wallet_management' => true,
            'real_time_transactions' => true,
        ],
        'endpoints' => [
            'authentication' => [
                'POST /api/auth/register',
                'POST /api/auth/login',
                'POST /api/auth/logout',
                'GET /api/auth/user',
            ],
            'user_features' => [
                'GET /api/user/profile',
                'PUT /api/user/profile',
                'GET /api/user/wallet',
                'POST /api/user/transactions/transfer',
                'POST /api/user/scan/confirm',
                'GET /api/user/history',
            ],
            'admin_features' => [
                'GET /api/admin/dashboard',
                'GET /api/admin/users',
                'POST /api/admin/dropboxes',
                'GET /api/admin/transactions',
                'POST /api/admin/topup-requests/{id}/approve',
            ],
        ]
    ]);
});

// Error handling for undefined routes
Route::fallback(function () {
    return response()->json([
        'success' => false,
        'message' => 'API endpoint not found',
        'error_code' => 'ENDPOINT_NOT_FOUND',
        'suggestion' => 'Please check the API documentation at GET /api/',
        'timestamp' => now()->toISOString(),
    ], 404);
});
