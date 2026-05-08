<?php

namespace App\Listeners;

use App\Events\CapeSubmittedEvent;
use App\Jobs\SendGoogleAnalyticsEvent;
use App\Services\AnalyticsService;

class CapeSubmittedListener
{
    public function __construct(public AnalyticsService $analytics) {}

    public function handle(CapeSubmittedEvent $event): void
    {
        if (! config('ga4.measurement_id') || ! config('ga4.secret')) {
            return;
        }

        SendGoogleAnalyticsEvent::dispatch(
            $this->analytics->buildCapeSubmittedRequest($event->username)
        );
    }
}
