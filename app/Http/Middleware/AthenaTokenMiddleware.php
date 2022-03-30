<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AthenaTokenMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $apiKey = $request->route('apiKey');
        if ($apiKey !== config('athena.general.apiKey')) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        return $next($request);
    }
}
