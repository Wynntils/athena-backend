<?php

namespace App\Http\Middleware;

use Br33f\Ga4\MeasurementProtocol\Dto\Event\BaseEvent;
use Br33f\Ga4\MeasurementProtocol\Dto\Request\BaseRequest;
use Br33f\Ga4\MeasurementProtocol\Exception\HydrationException;
use Br33f\Ga4\MeasurementProtocol\Exception\ValidationException;
use Br33f\Ga4\MeasurementProtocol\Service;
use Closure;
use Illuminate\Http\Request;

class Analytics
{
    public function __construct(public Service $ga4Service)
    {
    }

    public function handle(Request $request, Closure $next)
    {
        $userId = $this->getUserId($request);
        $user = $this->getUser($request);

        \Sentry\configureScope(function (\Sentry\State\Scope $scope) use ($user): void {
            if ($user) {
                $scope->setUser(['id' => $user->id, 'username' => $user->username]);
            }
        });

        // Create base request
        $baseRequest = new BaseRequest();
        $baseRequest->setUserId($userId);
        // Get the user agent
        $baseRequest->setClientId($request->userAgent());

        // Create event
        $pageViewEvent = new BaseEvent('page_view');
        $pageViewEvent->setParamValue('page_title', $request->path());
        $pageViewEvent->setParamValue('page_location', $request->fullUrl());
        $pageViewEvent->setParamValue('page_path', $request->path());
        $pageViewEvent->setParamValue('engagement_time_msec', '1');

        // Add event to base request
        $baseRequest->addEvent($pageViewEvent);

        // Send request
        try {
            $this->ga4Service->send($baseRequest);
        } catch (HydrationException|ValidationException $e) {
            //
        }

        return $next($request);
    }

    private function getUserId(Request $request)
    {
        $clientId = null;
        $user = $this->getUser($request);
        if ($user) {
            $clientId = $user->id;
        }

        return $clientId ?? $request->route('apiKey') ?? 'unknown';
    }

    private function getUser(Request $request) {
        if ($request->hasHeader('authToken')) {
            $authToken = $request->header('authToken');
            $user = \App\Models\User::where('authToken', $authToken)->first();
            return $user;
        }

        return null;
    }
}
