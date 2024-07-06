<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class BlockUserAgents
{


    /**
     * List of blocked user agents.
     *
     * @var array
     */
    protected array $blockedUserAgents = [
        "Mozilla/4.0",
    ];

    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure(Request): (Response|RedirectResponse) $next
     * @return Response|RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {

        $userAgent = $request->header('User-Agent');

        foreach($this->blockedUserAgents as $blockedUserAgent) {
            if(stripos($userAgent, $blockedUserAgent) !== false) {
                return response('This user agent is blocked', 403);
            }
        }

        return $next($request);
    }
}
