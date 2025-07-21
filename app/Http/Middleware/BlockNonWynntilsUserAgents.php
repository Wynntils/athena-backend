<?php

namespace App\Http\Middleware;

use Closure;

class BlockNonWynntilsUserAgents
{
    public function handle($request, Closure $next)
    {
        // Define allowed User-Agent prefix
        $allowedUserAgentPrefix = 'Wynntils Artemis';
        $userAgent = $request->header('User-Agent', '');

        // Always allow requests from the allowed user agent
        if (stripos($userAgent, $allowedUserAgentPrefix) === 0) {
            return $next($request);
        }

        // Allow all others only if API key is present and correct
        $apiKey = $request->input('apiKey') ?? $request->header('apiKey');
        if ($apiKey && $apiKey === config('athena.general.apiKey')) {
            return $next($request);
        }

        // Block everything else
        return response('This user agent is blocked', 403);
    }
}
