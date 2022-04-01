<?php

namespace App\Providers;

use App\Contracts\Libraries\MinecraftFakeAuthInterface;
use App\Http\Libraries\MinecraftFakeAuth;
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
        $this->app->singleton(MinecraftFakeAuthInterface::class, MinecraftFakeAuth::class);
    }
}
