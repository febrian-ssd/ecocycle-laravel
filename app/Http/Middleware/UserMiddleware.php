<?php
// app/Http/Middleware/UserMiddleware.php - CREATE NEW FILE

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

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
                'message' => 'Authentication required',
                'data' => null
            ], 401);
        }

        $user = Auth::user();

        // Check if user has user role (not admin)
        if (!$user->isUser()) {
            return response()->json([
                'success' => false,
                'message' => 'User access only. Admin users should use admin routes',
                'data' => null,
                'user_role' => $user->role
            ], 403);
        }

        // Check if user account is active
        if (!$user->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Your account has been deactivated',
                'data' => null
            ], 403);
        }

        return $next($request);
    }
}
