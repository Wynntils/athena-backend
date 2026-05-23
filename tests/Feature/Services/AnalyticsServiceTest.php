<?php

use App\Services\AnalyticsService;

uses(Tests\TestCase::class);

// buildCapeSubmittedRequest

it('returns a request with cape_submitted event and correct client_id', function () {
    $service = app(AnalyticsService::class);
    $baseRequest = $service->buildCapeSubmittedRequest('SomePlayer');
    $event = $baseRequest->getEvents()->getEventList()[0];
    expect($baseRequest->getClientId())->toBe(md5('SomePlayer'))
        ->and($event->getName())->toBe('cape_submitted')
        ->and($event->getParamValue('username'))->toBe('SomePlayer');
});
