<?php

namespace App\Providers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Http::macro('wynn', function () {
            return Http::withHeaders(['apiKey' => config('athena.api.wynn.apiKey')])
                ->withUserAgent(config('athena.general.userAgent'))
                ->connectTimeout(50)
                ->timeout(50);
        });
    }
}
