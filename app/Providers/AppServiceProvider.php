<?php

namespace App\Providers;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\ServiceProvider;
//use Laravel\Sanctum\Sanctum;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
//        Sanctum::ignoreMigrations();

        if ($this->app->environment('local') && class_exists(\Laravel\Telescope\TelescopeServiceProvider::class)) {
            $this->app->register(\Laravel\Telescope\TelescopeServiceProvider::class);
            $this->app->register(TelescopeServiceProvider::class);
        }
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Paginator::useBootstrapFive();

        Http::macro('wynn', function () {
            return Http::withHeaders([
                    'Authorization' => 'Bearer ' . config('athena.api.wynn.apiKey'),
                ])
                ->withUserAgent(config('athena.general.userAgent'))
                ->connectTimeout(50)
                ->timeout(50);
        });
    }
}
