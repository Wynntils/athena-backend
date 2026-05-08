<?php

use App\Events\LoginEvent;
use App\Jobs\SendGoogleAnalyticsEvent;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;

uses(Tests\TestCase::class, RefreshDatabase::class);

it('queues a job with client_id equal to user id', function () {
    Queue::fake();
    config(['ga4.measurement_id' => 'G-TEST123', 'ga4.secret' => 'test-secret']);

    $user = User::factory()->create();
    LoginEvent::dispatch($user, 'Wynntils Artemis\\v0.0.4+MC-1.21.4 (client) FABRIC', 'Minecraft');

    Queue::assertPushed(SendGoogleAnalyticsEvent::class, function ($job) use ($user) {
        return $job->baseRequest->getClientId() === $user->id;
    });
});

it('queues a job with user_id set correctly', function () {
    Queue::fake();
    config(['ga4.measurement_id' => 'G-TEST123', 'ga4.secret' => 'test-secret']);

    $user = User::factory()->create();
    LoginEvent::dispatch($user, 'Wynntils Artemis\\v0.0.4+MC-1.21.4 (client) FABRIC', 'Minecraft');

    Queue::assertPushed(SendGoogleAnalyticsEvent::class, function ($job) use ($user) {
        return $job->baseRequest->getUserId() === $user->id;
    });
});
