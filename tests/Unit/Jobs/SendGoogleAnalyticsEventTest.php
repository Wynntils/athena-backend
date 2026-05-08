<?php

namespace Tests\Unit\Jobs;

use App\Jobs\SendGoogleAnalyticsEvent;
use Br33f\Ga4\MeasurementProtocol\Dto\Request\BaseRequest;
use Br33f\Ga4\MeasurementProtocol\Exception\HydrationException;
use Br33f\Ga4\MeasurementProtocol\Exception\ValidationException;
use Br33f\Ga4\MeasurementProtocol\Service;
use Mockery;
use Tests\TestCase;

uses(TestCase::class);

it('has tries set to 3', function () {
    $job = new SendGoogleAnalyticsEvent(new BaseRequest);

    expect($job->tries)->toBe(3);
});

it('has backoff set to 10 and 30 seconds', function () {
    $job = new SendGoogleAnalyticsEvent(new BaseRequest);

    expect($job->backoff)->toBe([10, 30]);
});

it('swallows HydrationException without throwing', function () {
    $service = Mockery::mock(Service::class);
    $service->shouldReceive('send')->andThrow(new HydrationException('bad'));

    $job = new SendGoogleAnalyticsEvent(new BaseRequest);

    expect(fn () => $job->handle($service))->not->toThrow(\Throwable::class);
});

it('swallows ValidationException without throwing', function () {
    $service = Mockery::mock(Service::class);
    $service->shouldReceive('send')->andThrow(new ValidationException('bad'));

    $job = new SendGoogleAnalyticsEvent(new BaseRequest);

    expect(fn () => $job->handle($service))->not->toThrow(\Throwable::class);
});

it('propagates other exceptions to trigger retries', function () {
    $service = Mockery::mock(Service::class);
    $service->shouldReceive('send')->andThrow(new \RuntimeException('network error'));

    $job = new SendGoogleAnalyticsEvent(new BaseRequest);

    expect(fn () => $job->handle($service))->toThrow(\RuntimeException::class);
});
