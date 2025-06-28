<?php
// app/Http/Controllers/Api/AuthController.php - COMPLETE UPDATE
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
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
            if (!Auth::attempt($request->only('email', 'password'))) {
                return ApiResponse::error('Invalid credentials', 401);
            }

            $user = Auth::user();

            if (!$user->is_active) {
                Auth::logout();
                return ApiResponse::error('Account deactivated', 403);
            }

            $token = $user->createToken('auth_token', [$user->role])->plainTextToken;

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
            ], 'Login successful');

        } catch (\Exception $e) {
            return ApiResponse::error('Login failed: ' . $e->getMessage(), 500);
        }
    }

    public function user(Request $request)
    {
        try {
            $user = $request->user();
            return ApiResponse::success([
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'balance_rp' => $user->balance_rp,
                'balance_coins' => $user->balance_coins,
                'is_admin' => $user->isAdmin(),
                'is_active' => $user->is_active,
            ], 'User data retrieved successfully');
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to get user data: ' . $e->getMessage(), 500);
        }
    }

    public function logout(Request $request)
    {
        try {
            $request->user()->currentAccessToken()->delete();
            return ApiResponse::success(null, 'Logout successful');
        } catch (\Exception $e) {
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

            return ApiResponse::success([
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
            ], 'Profile updated successfully');

        } catch (\Exception $e) {
            return ApiResponse::error('Profile update failed: ' . $e->getMessage(), 500);
        }
    }

    public function updateAdminProfile(Request $request)
    {
        return $this->updateProfile($request);
    }
}
