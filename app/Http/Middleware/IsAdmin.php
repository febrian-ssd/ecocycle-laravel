<?php
// app/Http/Middleware/IsAdmin.php - COMPLETE UPDATE

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class IsAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Authentication required',
                    'data' => null
                ], 401);
            }
            return redirect()->route('login')->with('error', 'Please login to continue.');
        }

        $user = Auth::user();

        // Check if user is admin
        if (!$user->isAdmin()) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Admin access required',
                    'data' => null,
                    'user_role' => $user->role
                ], 403);
            }
            return redirect()->route('home')->with('error', 'Admin access required.');
        }

        // Check if admin account is active
        if (!$user->is_active) {
            Auth::logout();
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Admin account deactivated',
                    'data' => null
                ], 403);
            }
            return redirect()->route('login')->with('error', 'Admin account deactivated.');
        }

        return $next($request);
    }
}
