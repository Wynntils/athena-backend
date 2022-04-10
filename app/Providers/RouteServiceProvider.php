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
     * The path to the "home" route for your application.
     *
     * This is used by Laravel authentication to redirect users after login.
     *
     * @var string
     */
    public const HOME = '/home';

    /**
     * Define your route model bindings, pattern filters, etc.
     *
     * @return void
     */
    public function boot()
    {
        $this->configureRateLimiting();

        $this->routes(function () {
            Route::prefix('api')
                ->middleware('api')
                ->group(base_path('routes/api.php'));

            Route::prefix('auth')
                ->middleware('api')
                ->group(base_path('routes/auth.php'));

            Route::prefix('user')
                ->middleware('api')
                ->group(base_path('routes/user.php'));

            Route::prefix('cache')
                ->middleware('api')
                ->group(base_path('routes/cache.php'));

            Route::prefix('capes')
                ->middleware('api')
                ->group(base_path('routes/cape.php'));

            Route::prefix('telemetry')
                ->middleware('api')
                ->group(base_path('routes/telemetry.php'));

            Route::prefix('patreon')
                ->middleware('api')
                ->group(base_path('routes/patreon.php'));

            Route::middleware('web')
                ->group(base_path('routes/web.php'));
        });
    }

    /**
     * Configure the rate limiters for the application.
     *
     * @return void
     */
    protected function configureRateLimiting()
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });
    }
}
