<?php
namespace App\Http\Middleware;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use App\Models\User;

class AdminMiddleware
{
    /**
     * Handle an incoming request for admin-only routes.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Authentication required.',
                    'error_code' => 'UNAUTHENTICATED'
                ], 401);
            }
            return redirect()->route('login')->with('error', 'Please login to continue.');
        }

        $user = Auth::user();

        // Check if user is admin
        if (!$user->isAdmin()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Admin access required.',
                    'error_code' => 'ADMIN_REQUIRED',
                    'user_role' => $user->role
                ], 403);
            }
            return redirect()->route('home')->with('error', 'Admin access required.');
        }

        // Check if admin account is active
        if (!$user->is_active) {
            Auth::logout();
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Admin account deactivated.',
                    'error_code' => 'ADMIN_DEACTIVATED'
                ], 403);
            }
            return redirect()->route('login')->with('error', 'Admin account deactivated.');
        }

        return $next($request);
    }
}
