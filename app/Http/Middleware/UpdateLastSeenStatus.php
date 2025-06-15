<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Models\User;

class UpdateLastSeenStatus
{
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check()) {
            // Perbarui kolom last_seen untuk user yang sedang login
            User::where('id', Auth::id())->update(['last_seen' => Carbon::now()]);
        }
        return $next($request);
    }
}
