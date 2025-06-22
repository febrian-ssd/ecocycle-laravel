<?php

// app/Http/Middleware/AdminMiddleware.php - BUAT FILE BARU INI
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if user is authenticated
        if (!Auth::check()) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }
            return redirect()->route('login')->with('error', 'Anda harus login terlebih dahulu.');
        }

        // Check if user is admin
        if (!Auth::user()->is_admin) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthorized. Admin access required.'], 403);
            }
            abort(403, 'Unauthorized. Admin access required.');
        }

        return $next($request);
    }
}

// ======================================================================

// app/Http/Middleware/UpdateLastSeenStatus.php - PERBAIKAN
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class UpdateLastSeenStatus
{
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check()) {
            // Update last_seen atau updated_at untuk tracking user activity
            try {
                $user = Auth::user();
                $user->updated_at = Carbon::now();
                $user->save();
            } catch (\Exception $e) {
                // Silent fail jika ada error update
            }
        }

        return $next($request);
    }
}

// ======================================================================

// app/Http/Middleware/EnsureUserHasBalance.php - MIDDLEWARE BARU UNTUK API
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class EnsureUserHasBalance
{
    /**
     * Handle an incoming request.
     * Middleware untuk memastikan user punya kolom balance yang valid
     */
    public function handle(Request $request, Closure $next)
    {
        if ($request->user()) {
            $user = $request->user();

            // Pastikan kolom balance ada dan tidak null
            if (is_null($user->balance_rp)) {
                $user->balance_rp = 0;
                $user->save();
            }

            if (is_null($user->balance_coins)) {
                $user->balance_coins = 0;
                $user->save();
            }
        }

        return $next($request);
    }
}
