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
    public const HOME = '/crash';

    /**
     * Define your route model bindings, pattern filters, etc.
     *
     * @return void
     */
    public function boot()
    {
        $this->configureRateLimiting();

        $this->routes(function () {
            Route::middleware('api')->group(function () {
                foreach (glob(base_path('routes/api/*.php')) as $file) {
                    // prefix based on file name
                    Route::prefix(basename($file, '.php'))
                        ->name(basename($file, '.php') . '.')
                        ->group($file);
                }
            });
//            Route::prefix('api')->group(base_path('routes/api.php'));
//            Route::prefix('auth')->group(base_path('routes/auth.php'));
//            Route::prefix('version')->name('version.')->group(base_path('routes/version.php'));
//            Route::prefix('user')->group(base_path('routes/user.php'));
//            Route::prefix('cache')->group(base_path('routes/cache.php'));
//            Route::prefix('capes')->group(base_path('routes/cape.php'));
//            Route::prefix('telemetry')->group(base_path('routes/telemetry.php'));
//            Route::prefix('patreon')->group(base_path('routes/patreon.php'));

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
