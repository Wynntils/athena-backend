<?php

use App\Models\User;
use App\Services\AnalyticsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

uses(Tests\TestCase::class, \Illuminate\Foundation\Testing\RefreshDatabase::class);

// shouldSkip

it('skips telescope paths', function () {
    $service = app(AnalyticsService::class);
    expect($service->shouldSkip(Request::create('/telescope/requests')))->toBeTrue();
});

it('skips horizon paths', function () {
    $service = app(AnalyticsService::class);
    expect($service->shouldSkip(Request::create('/horizon/dashboard')))->toBeTrue();
});

it('skips api/docs paths', function () {
    $service = app(AnalyticsService::class);
    expect($service->shouldSkip(Request::create('/api/docs')))->toBeTrue();
});

it('skips docs paths', function () {
    $service = app(AnalyticsService::class);
    expect($service->shouldSkip(Request::create('/docs/api-docs.json')))->toBeTrue();
});

it('skips oauth paths', function () {
    $service = app(AnalyticsService::class);
    expect($service->shouldSkip(Request::create('/oauth/microsoft')))->toBeTrue();
});

it('skips auth paths', function () {
    $service = app(AnalyticsService::class);
    expect($service->shouldSkip(Request::create('/auth/login')))->toBeTrue();
});

it('skips crash paths', function () {
    $service = app(AnalyticsService::class);
    expect($service->shouldSkip(Request::create('/crash/view/1')))->toBeTrue();
});

it('skips phpinfo paths', function () {
    $service = app(AnalyticsService::class);
    expect($service->shouldSkip(Request::create('/phpinfo')))->toBeTrue();
});

it('does not skip normal api paths', function () {
    $service = app(AnalyticsService::class);
    expect($service->shouldSkip(Request::create('/api/v1/version/latest/re')))->toBeFalse();
});

// resolveUserId

it('returns null when no authToken header is present', function () {
    $service = app(AnalyticsService::class);
    $request = Request::create('/api/test');
    expect($service->resolveUserId($request))->toBeNull();
});

it('returns null when authToken header is empty', function () {
    $service = app(AnalyticsService::class);
    $request = Request::create('/api/test');
    $request->headers->set('authToken', '');
    expect($service->resolveUserId($request))->toBeNull();
});

it('returns null when authToken does not match any user', function () {
    $service = app(AnalyticsService::class);
    Cache::flush();
    $request = Request::create('/api/test');
    $request->headers->set('authToken', 'nonexistent-token');
    expect($service->resolveUserId($request))->toBeNull();
});

it('returns user uuid when authToken resolves', function () {
    $user = User::factory()->create(['auth_token' => 'my-valid-token']);
    $service = app(AnalyticsService::class);
    Cache::flush();
    $request = Request::create('/api/test');
    $request->headers->set('authToken', 'my-valid-token');
    expect($service->resolveUserId($request))->toBe($user->id);
});

// resolveClientId

it('returns the userId when provided', function () {
    $service = app(AnalyticsService::class);
    $request = Request::create('/api/test');
    expect($service->resolveClientId($request, 'some-uuid'))->toBe('some-uuid');
});

it('returns md5 of ip and user agent when userId is null', function () {
    $service = app(AnalyticsService::class);
    $request = Request::create('/api/test', 'GET', [], [], [], [
        'REMOTE_ADDR' => '1.2.3.4',
        'HTTP_USER_AGENT' => 'TestAgent/1.0',
    ]);
    expect($service->resolveClientId($request, null))->toBe(md5('1.2.3.4'.'TestAgent/1.0'));
});

// parseUserAgent

it('extracts version, mc_version, and modloader from a Wynntils UA', function () {
    $service = app(AnalyticsService::class);
    $result = $service->parseUserAgent('Wynntils Artemis\\v0.0.4+MC-1.21.4 (client) FABRIC');
    expect($result['wynntils_version'])->toBe('0.0.4')
        ->and($result['mc_version'])->toBe('1.21.4')
        ->and($result['modloader'])->toBe('fabric');
});

it('returns nulls for an unrecognized user agent', function () {
    $service = app(AnalyticsService::class);
    $result = $service->parseUserAgent('Mozilla/5.0 (Windows NT 10.0; Win64; x64)');
    expect($result['wynntils_version'])->toBeNull()
        ->and($result['mc_version'])->toBeNull()
        ->and($result['modloader'])->toBeNull();
});

// buildPageViewRequest

it('does not set userId on the request when user is not resolved', function () {
    $service = app(AnalyticsService::class);
    $request = Request::create('/api/test');
    $baseRequest = $service->buildPageViewRequest($request);
    expect($baseRequest->getUserId())->toBeNull();
});

it('sets userId when authToken resolves', function () {
    $user = User::factory()->create(['auth_token' => 'token-for-page-view']);
    $service = app(AnalyticsService::class);
    Cache::flush();
    $request = Request::create('/api/test');
    $request->headers->set('authToken', 'token-for-page-view');
    $baseRequest = $service->buildPageViewRequest($request);
    expect($baseRequest->getUserId())->toBe($user->id);
});

// buildCapeSubmittedRequest

it('returns a request with cape_submitted event and correct client_id', function () {
    $service = app(AnalyticsService::class);
    $baseRequest = $service->buildCapeSubmittedRequest('SomePlayer');
    $event = $baseRequest->getEvents()->getEventList()[0];
    expect($baseRequest->getClientId())->toBe(md5('SomePlayer'))
        ->and($event->getName())->toBe('cape_submitted');
});
