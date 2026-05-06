<?php

namespace App\Providers;

use Dedoc\Scramble\Scramble;
use Dedoc\Scramble\Support\Generator\OpenApi;
use Dedoc\Scramble\Support\Generator\SecurityScheme;
use Illuminate\Pagination\Paginator;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\ServiceProvider;

// use Laravel\Sanctum\Sanctum;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Route prefixes that should appear in the Scramble-generated API documentation.
     * Any route whose URI does not begin with one of these prefixes is excluded.
     *
     * @var string[]
     */
    private const DOCUMENTED_ROUTE_PREFIXES = [
        'auth/',
        'cache/',
        'capes/',
        'crash/',
        'user/',
        'telemetry/',
        'version/',
        'v2/',
        'api/',
        'guilds/',
        'webhook/',
        'patreon/',
    ];

    /**
     * Register any application services.
     */
    public function register(): void
    {
        //        Sanctum::ignoreMigrations();

        if ($this->app->environment('local') && class_exists(\Laravel\Telescope\TelescopeServiceProvider::class)) {
            $this->app->register(\Laravel\Telescope\TelescopeServiceProvider::class);
            $this->app->register(TelescopeServiceProvider::class);
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::useBootstrapFive();

        Http::macro('wynn', function () {
            return Http::withHeaders([
                'Authorization' => 'Bearer '.config('athena.api.wynn.apiKey'),
            ])
                ->withUserAgent(config('athena.general.userAgent'))
                ->connectTimeout(50)
                ->timeout(50);
        });

        $this->configureScramble();
    }

    /**
     * Configure Scramble API documentation generation.
     *
     * - Ignores default `/docs/api` routes and exposes docs at `/api/docs` instead.
     * - Uses a prefix-allowlist to include only public API routes.
     * - Registers the `AuthToken` apiKey security scheme so protected endpoints
     *   display the lock icon in the docs UI.
     */
    private function configureScramble(): void
    {
        // Prevent Scramble from registering its default /docs/api and /docs/api.json routes.
        Scramble::ignoreDefaultRoutes();

        // Custom route resolver: only include routes whose URI starts with an allowed prefix.
        Scramble::configure()
            ->routes(function (Route $route) {
                foreach (self::DOCUMENTED_ROUTE_PREFIXES as $prefix) {
                    if (str_starts_with($route->uri, $prefix)) {
                        return true;
                    }
                }

                return false;
            })
            ->expose(
                ui: 'api/docs',
                document: 'api/docs.json',
            );

        // Register the AuthToken apiKey security scheme (header-based).
        // This matches the scheme previously defined in app/Docs/OpenAPI.php.
        Scramble::afterOpenApiGenerated(function (OpenApi $openApi) {
            $openApi->secure(
                SecurityScheme::apiKey('header', 'authToken')
                    ->as('AuthToken')
            );
        });
    }
}
