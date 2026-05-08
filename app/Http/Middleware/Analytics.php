<?php

namespace App\Http\Middleware;

use App\Jobs\SendGoogleAnalyticsEvent;
use App\Services\AnalyticsService;
use Closure;
use Illuminate\Http\Request;

class Analytics
{
    public function __construct(public AnalyticsService $analytics) {}

    public function handle(Request $request, Closure $next): mixed
    {
        if (! config('ga4.measurement_id') || ! config('ga4.secret')) {
            return $next($request);
        }

        if ($this->analytics->shouldSkip($request)) {
            return $next($request);
        }

        app()->terminating(function () use ($request) {
            SendGoogleAnalyticsEvent::dispatch(
                $this->analytics->buildPageViewRequest($request)
            );
        });

        return $next($request);
    }
}
