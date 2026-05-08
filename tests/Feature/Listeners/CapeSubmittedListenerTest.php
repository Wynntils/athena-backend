<?php

use App\Events\CapeSubmittedEvent;
use App\Jobs\SendGoogleAnalyticsEvent;
use Illuminate\Support\Facades\Queue;

uses(Tests\TestCase::class);

it('queues a SendGoogleAnalyticsEvent job with cape_submitted event', function () {
    Queue::fake();
    config(['ga4.measurement_id' => 'G-TEST123', 'ga4.secret' => 'test-secret']);

    CapeSubmittedEvent::dispatch('SomePlayer');

    Queue::assertPushed(SendGoogleAnalyticsEvent::class, function ($job) {
        $event = $job->baseRequest->getEvents()->getEventList()[0];

        return $event->getName() === 'cape_submitted'
            && $job->baseRequest->getClientId() === md5('SomePlayer');
    });
});

it('does not queue a job when GA4 is not configured', function () {
    Queue::fake();
    config(['ga4.measurement_id' => null, 'ga4.secret' => null]);

    CapeSubmittedEvent::dispatch('SomePlayer');

    Queue::assertNotPushed(SendGoogleAnalyticsEvent::class);
});
