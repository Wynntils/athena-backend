<?php

namespace App\Listeners;

use App\Events\LoginEvent;
use App\Jobs\SendGoogleAnalyticsEvent;
use Br33f\Ga4\MeasurementProtocol\Dto\Common\UserProperties;
use Br33f\Ga4\MeasurementProtocol\Dto\Common\UserProperty;
use Br33f\Ga4\MeasurementProtocol\Dto\Event\LoginEvent as GALoginEvent;
use Br33f\Ga4\MeasurementProtocol\Dto\Request\BaseRequest;

class LoginEventListener
{
    public function handle(LoginEvent $event): void
    {
        // Skip if GA4 is not configured
        if (! config('ga4.measurement_id') || ! config('ga4.secret')) {
            return;
        }

        // Create base request
        $baseRequest = new BaseRequest;
        $baseRequest->setClientId($event->userAgent);
        $baseRequest->setUserId($event->user->id);
        $baseRequest->setUserProperties(new UserProperties([
            new UserProperty('version', $event->user->latest_version),
        ]));

        // Create event
        $loginEvent = new GALoginEvent;
        $loginEvent->setMethod($event->method);
        $loginEvent->setParamValue('engagement_time_msec', '1');

        // Add event to base request
        $baseRequest->addEvent($loginEvent);

        // Queue the analytics event to avoid blocking the request
        SendGoogleAnalyticsEvent::dispatch($baseRequest);
    }
}
