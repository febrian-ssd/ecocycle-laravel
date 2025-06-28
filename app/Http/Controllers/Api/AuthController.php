<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use App\Helpers\ApiResponse;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
            'role' => 'sometimes|string|in:admin,user'
        ]);

        if ($validator->fails()) {
            return ApiResponse::validationError($validator->errors());
        }

        try {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => $request->role ?? User::ROLE_USER,
                'balance_rp' => 0,
                'balance_coins' => 0,
                'is_active' => true,
            ]);

            $token = $user->createToken('auth_token', [$user->role])->plainTextToken;

            Log::info('User registered successfully', ['user_id' => $user->id, 'email' => $user->email]);

            return ApiResponse::success([
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                    'balance_rp' => $user->balance_rp,
                    'balance_coins' => $user->balance_coins,
                    'is_admin' => $user->isAdmin(),
                ],
                'access_token' => $token,
                'token_type' => 'Bearer',
            ], 'Registration successful', 201);

        } catch (\Exception $e) {
            Log::error('Registration failed', ['error' => $e->getMessage(), 'email' => $request->email]);
            return ApiResponse::error('Registration failed: ' . $e->getMessage(), 500);
        }
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return ApiResponse::validationError($validator->errors());
        }

        try {
            Log::info('Login attempt', [
                'email' => $request->email,
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);

            // Check if user exists
            $user = User::where('email', $request->email)->first();

            if (!$user) {
                Log::warning('Login failed - user not found', ['email' => $request->email]);
                return ApiResponse::error('Invalid credentials', 401);
            }

            if (!Hash::check($request->password, $user->password)) {
                Log::warning('Login failed - invalid password', ['email' => $request->email]);
                return ApiResponse::error('Invalid credentials', 401);
            }

            if (!$user->is_active) {
                Log::warning('Login failed - account deactivated', ['email' => $request->email]);
                return ApiResponse::error('Account deactivated', 403);
            }

            // Login successful
            Auth::login($user);
            $token = $user->createToken('auth_token', [$user->role])->plainTextToken;

            Log::info('Login successful', [
                'user_id' => $user->id,
                'email' => $user->email,
                'role' => $user->role
            ]);

            return ApiResponse::success([
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                    'balance_rp' => (float) ($user->balance_rp ?? 0),
                    'balance_coins' => (int) ($user->balance_coins ?? 0),
                    'is_admin' => $user->isAdmin(),
                ],
                'access_token' => $token,
                'token_type' => 'Bearer',
            ], 'Login successful');

        } catch (\Exception $e) {
            Log::error('Login exception', [
                'error' => $e->getMessage(),
                'email' => $request->email,
                'trace' => $e->getTraceAsString()
            ]);
            return ApiResponse::error('Login failed: ' . $e->getMessage(), 500);
        }
    }

    public function user(Request $request)
    {
        try {
            $user = $request->user();

            if (!$user) {
                return ApiResponse::error('User not authenticated', 401);
            }

            return ApiResponse::success([
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'balance_rp' => (float) ($user->balance_rp ?? 0),
                'balance_coins' => (int) ($user->balance_coins ?? 0),
                'is_admin' => $user->isAdmin(),
                'is_active' => $user->is_active,
                'created_at' => $user->created_at,
            ], 'User data retrieved successfully');

        } catch (\Exception $e) {
            Log::error('Get user failed', ['error' => $e->getMessage()]);
            return ApiResponse::error('Failed to get user data: ' . $e->getMessage(), 500);
        }
    }

    public function logout(Request $request)
    {
        try {
            $user = $request->user();

            if ($user) {
                $request->user()->currentAccessToken()->delete();
                Log::info('User logged out', ['user_id' => $user->id]);
            }

            return ApiResponse::success(null, 'Logout successful');

        } catch (\Exception $e) {
            Log::error('Logout failed', ['error' => $e->getMessage()]);
            return ApiResponse::error('Logout failed: ' . $e->getMessage(), 500);
        }
    }

    public function updateProfile(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,' . $request->user()->id,
            'password' => 'sometimes|string|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return ApiResponse::validationError($validator->errors());
        }

        try {
            $user = $request->user();
            $data = $request->only('name', 'email');

            if ($request->filled('password')) {
                $data['password'] = Hash::make($request->password);
            }

            $user->update($data);

            Log::info('Profile updated', ['user_id' => $user->id]);

            return ApiResponse::success([
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
            ], 'Profile updated successfully');

        } catch (\Exception $e) {
            Log::error('Profile update failed', ['error' => $e->getMessage()]);
            return ApiResponse::error('Profile update failed: ' . $e->getMessage(), 500);
        }
    }

    public function updateAdminProfile(Request $request)
    {
        if (!$request->user()->isAdmin()) {
            return ApiResponse::error('Admin access required', 403);
        }

        return $this->updateProfile($request);
    }
}
