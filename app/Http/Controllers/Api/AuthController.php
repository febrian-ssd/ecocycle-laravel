<?php
// app/Http/Controllers/Api/AuthController.php - Enhanced with Role Management

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use App\Models\User;

class AuthController extends Controller
{
    /**
     * Register a new user
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
            'role' => 'sometimes|string|in:admin,user'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => $request->role ?? User::ROLE_USER, // Default to user role
                'balance_rp' => 0,
                'balance_coins' => 0,
                'is_active' => true,
            ]);

            // Create token with role information
            $token = $user->createToken('auth_token', [$user->role])->plainTextToken;

            return response()->json([
                'success' => true,
                'message' => 'Registration successful',
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'role' => $user->role,
                        'role_display' => $user->getRoleDisplayName(),
                        'balance_rp' => $user->balance_rp,
                        'balance_coins' => $user->balance_coins,
                        'is_admin' => $user->isAdmin(),
                        'is_user' => $user->isUser(),
                    ],
                    'access_token' => $token,
                    'token_type' => 'Bearer',
                ]
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Registration failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Login user
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $credentials = $request->only('email', 'password');

            if (!Auth::attempt($credentials)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid credentials',
                    'error_code' => 'INVALID_CREDENTIALS'
                ], 401);
            }

            $user = Auth::user();

            // Check if account is active
            if (!$user->is_active) {
                Auth::logout();
                return response()->json([
                    'success' => false,
                    'message' => 'Your account has been deactivated. Please contact support.',
                    'error_code' => 'ACCOUNT_DEACTIVATED'
                ], 403);
            }

            // Create token with role-based abilities
            $abilities = [$user->role];
            if ($user->isAdmin()) {
                $abilities[] = 'admin:access';
                $abilities[] = 'user:manage';
            } else {
                $abilities[] = 'user:access';
            }

            $token = $user->createToken('auth_token', $abilities)->plainTextToken;

            return response()->json([
                'success' => true,
                'message' => 'Login successful',
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'role' => $user->role,
                        'role_display' => $user->getRoleDisplayName(),
                        'balance_rp' => $user->balance_rp,
                        'balance_coins' => $user->balance_coins,
                        'is_admin' => $user->isAdmin(),
                        'is_user' => $user->isUser(),
                    ],
                    'access_token' => $token,
                    'token_type' => 'Bearer',
                    'abilities' => $abilities,
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Login failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get authenticated user info
     */
    public function user(Request $request)
    {
        try {
            $user = $request->user();

            return response()->json([
                'success' => true,
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'role' => $user->role,
                        'role_display' => $user->getRoleDisplayName(),
                        'balance_rp' => $user->balance_rp,
                        'balance_coins' => $user->balance_coins,
                        'is_admin' => $user->isAdmin(),
                        'is_user' => $user->isUser(),
                        'is_active' => $user->is_active,
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get user data',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Logout user
     */
    public function logout(Request $request)
    {
        try {
            // Revoke current token
            $request->user()->currentAccessToken()->delete();

            return response()->json([
                'success' => true,
                'message' => 'Logout successful'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Logout failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Logout from all devices
     */
    public function logoutAll(Request $request)
    {
        try {
            // Revoke all tokens for the user
            $request->user()->tokens()->delete();

            return response()->json([
                'success' => true,
                'message' => 'Logged out from all devices successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Logout from all devices failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Check token validity and permissions
     */
    public function checkToken(Request $request)
    {
        try {
            $user = $request->user();
            $token = $request->user()->currentAccessToken();

            return response()->json([
                'success' => true,
                'data' => [
                    'valid' => true,
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'role' => $user->role,
                        'is_admin' => $user->isAdmin(),
                        'is_user' => $user->isUser(),
                    ],
                    'token' => [
                        'name' => $token->name,
                        'abilities' => $token->abilities,
                        'created_at' => $token->created_at,
                        'last_used_at' => $token->last_used_at,
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Token validation failed',
                'error' => $e->getMessage()
            ], 401);
        }
    }
}
