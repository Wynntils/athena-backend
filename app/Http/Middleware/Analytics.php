<?php

namespace App\Http\Middleware;

use App\Jobs\SendGoogleAnalyticsEvent;
use Br33f\Ga4\MeasurementProtocol\Dto\Event\BaseEvent;
use Br33f\Ga4\MeasurementProtocol\Dto\Request\BaseRequest;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class Analytics
{
    public function handle(Request $request, Closure $next)
    {
        // Skip if GA4 is not configured
        if (! config('ga4.measurement_id') || ! config('ga4.secret')) {
            return $next($request);
        }

        // Defer analytics processing until after response is sent
        $this->dispatchAnalyticsAfterResponse($request);

        return $next($request);
    }

    private function dispatchAnalyticsAfterResponse(Request $request): void
    {
        app()->terminating(function () use ($request) {
            $userId = $this->getUserId($request);

            // Create base request
            $baseRequest = new BaseRequest;
            $baseRequest->setUserId($userId);
            $baseRequest->setClientId($request->userAgent() ?? 'unknown');

            // Create event
            $pageViewEvent = new BaseEvent('page_view');
            $pageViewEvent->setParamValue('page_title', $request->path());
            $pageViewEvent->setParamValue('page_location', $request->fullUrl());
            $pageViewEvent->setParamValue('page_path', $request->path());
            $pageViewEvent->setParamValue('engagement_time_msec', '1');

            $baseRequest->addEvent($pageViewEvent);

            // Dispatch job to send analytics event asynchronously
            SendGoogleAnalyticsEvent::dispatch($baseRequest);
        });
    }

    private function getUserId(Request $request): string
    {
        // Check for authToken header first (most common case)
        if ($request->hasHeader('authToken')) {
            $authToken = $request->header('authToken');

            // Skip if auth token is empty or invalid
            if (empty($authToken) || ! is_string($authToken)) {
                return 'unknown';
            }

            // Cache user ID lookup for 5 minutes to avoid repeated DB queries
            return Cache::remember(
                "analytics_user_id:{$authToken}",
                300,
                function () use ($authToken) {
                    $user = \App\Models\User::where('auth_token', $authToken)
                        ->select('id')
                        ->first();

                    return $user?->id ?? 'unknown';
                }
            );
        }

        return $request->route('apiKey') ?? 'unknown';
    }
}
