<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use App\Models\User;

class UserMiddleware
{
    /**
     * Handle an incoming request for user-only routes.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Authentication required.',
                'error_code' => 'UNAUTHENTICATED'
            ], 401);
        }

        $user = Auth::user();

        // Check if user has user role (not admin)
        if (!$user->isUser()) {
            return response()->json([
                'success' => false,
                'message' => 'User access only. Admin users should use admin routes.',
                'error_code' => 'USER_ACCESS_ONLY',
                'user_role' => $user->role
            ], 403);
        }

        // Check if user account is active
        if (!$user->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Your account has been deactivated.',
                'error_code' => 'ACCOUNT_DEACTIVATED'
            ], 403);
        }

        return $next($request);
    }
}
