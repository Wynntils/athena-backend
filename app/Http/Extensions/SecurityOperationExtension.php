<?php

namespace App\Http\Extensions;

use Dedoc\Scramble\Extensions\OperationExtension;
use Dedoc\Scramble\Support\Generator\Operation;
use Dedoc\Scramble\Support\Generator\SecurityRequirement;
use Dedoc\Scramble\Support\RouteInfo;

class SecurityOperationExtension extends OperationExtension
{
    private const AUTHENTICATED_MIDDLEWARE = [
        'athena.token',
        'auth:token',
    ];

    public function handle(Operation $operation, RouteInfo $routeInfo): void
    {
        $middleware = $routeInfo->route->middleware();

        foreach (self::AUTHENTICATED_MIDDLEWARE as $protected) {
            if (in_array($protected, $middleware, true)) {
                $operation->addSecurity(new SecurityRequirement(['AuthToken' => []]));

                return;
            }
        }

        $operation->security = [];
    }
}
