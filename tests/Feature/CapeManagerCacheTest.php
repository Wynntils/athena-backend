<?php

use App\Http\Libraries\CapeManager;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

uses(Tests\TestCase::class);

beforeEach(function () {
    Storage::fake('approved');
    Storage::fake('special');
});

it('caches the cape base64 result with a 30-day TTL', function () {
    Storage::disk('approved')->put('sha1hash', 'raw_image_data');

    Cache::spy();

    CapeManager::instance()->getCapeAsBase64('sha1hash', true);

    Cache::shouldHaveReceived('remember')
        ->withArgs(fn ($key, $ttl) => $key === 'cape-texture-sha1hash-1' && $ttl === 2592000)
        ->once();
});

it('uses a different cache key for omitDefaultCape false', function () {
    Storage::disk('approved')->put('sha1hash', 'raw_image_data');
    Storage::disk('special')->put('defaultCape', 'default_data');

    Cache::spy();

    CapeManager::instance()->getCapeAsBase64('sha1hash', false);

    Cache::shouldHaveReceived('remember')
        ->withArgs(fn ($key, $ttl) => $key === 'cape-texture-sha1hash-0' && $ttl === 2592000)
        ->once();
});

it('does not cache results on special dates', function () {
    $manager = Mockery::mock(CapeManager::class)->makePartial();
    $manager->shouldReceive('isSpecialDate')->andReturn(true);
    $manager->shouldReceive('getSpecialCape')->andReturn('special_data');

    Cache::spy();

    $manager->getCapeAsBase64('sha1hash', true);

    Cache::shouldNotHaveReceived('remember');
});
