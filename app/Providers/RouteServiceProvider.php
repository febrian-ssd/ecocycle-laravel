<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to your application's "home" route.
     *
     * Typically, users are redirected here after authentication.
     *
     * @var string
     */
    public const HOME = '/'; // Pengaturan ini sudah benar dari perbaikan kita sebelumnya

    /**
     * Define your route model bindings, pattern filters, and other route configuration.
     */
    public function boot(): void
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        // Bagian ini akan memastikan kedua file rute dimuat dengan benar dan terpisah
        $this->routes(function () {
            // Rute API akan memiliki awalan /api dan menggunakan middleware 'api'
            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/api.php'));

            // Rute Web akan menggunakan middleware 'web' (yang berisi CSRF, session, dll)
            Route::middleware('web')
                ->group(base_path('routes/web.php'));
        });
    }
}
