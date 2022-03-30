<?php

namespace App\Providers;

use App\Models\ApiKey;
use App\Models\User;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        Auth::viaRequest('apiKey', static function (Request $request) {
            return ApiKey::findOrFail($request->header('apiKey') ?? $request->route('apiKey'))->first();
        });

        Auth::viaRequest('authToken', static function (Request $request) {
            return User::where('authToken', $request->header('authToken') ?? $request->input('authToken'))->first();
        });
    }
}
