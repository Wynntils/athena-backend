<?php

namespace App\Http\Middleware;

use App\Http\Libraries\CapeManager;
use Closure;
use Illuminate\Http\Request;

class CapeTokenMiddleware
{
    public function __construct(private CapeManager $capeManager) {}

    public function handle(Request $request, Closure $next)
    {
        $token = $request->header('token') ?? $request->route('token');
        if ($token === config('athena.general.apiKey') || $token === $this->capeManager->getToken()) {
            return $next($request);
        }

        return response()->json(['message' => 'Unauthorized'], 401);
    }
}
