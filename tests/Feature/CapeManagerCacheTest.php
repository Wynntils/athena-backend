<?php

use App\Enums\CosmeticSlot;
use App\Enums\CosmeticStatus;
use App\Enums\CosmeticType;
use App\Enums\CosmeticVisibility;
use App\Http\Libraries\CapeManager;
use App\Models\CosmeticAsset;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

uses(Tests\TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    Storage::fake('approved');
    Storage::fake('special');
});

afterEach(function () {
    app()->forgetInstance(CapeManager::class);
});

it('caches the cape base64 result with a 30-day TTL', function () {
    Storage::disk('approved')->put('sha1hash', 'raw_image_data');

    Cache::spy();

    app(CapeManager::class)->getCapeAsBase64('sha1hash', true);

    Cache::shouldHaveReceived('remember')
        ->withArgs(fn ($key, $ttl) => $key === 'cape-texture-sha1hash-1' && $ttl === 2592000)
        ->once();
});

it('uses a different cache key for omitDefaultCape false', function () {
    Storage::disk('approved')->put('sha1hash', 'raw_image_data');
    Storage::disk('special')->put('defaultCape', 'default_data');

    Cache::spy();

    app(CapeManager::class)->getCapeAsBase64('sha1hash', false);

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

it('listCapes stores result in cache with 24h TTL', function () {
    $png = imagecreatetruecolor(64, 32);
    ob_start();
    imagepng($png);
    $data = ob_get_clean();
    imagedestroy($png);

    Storage::disk('approved')->put('deadbeef1234deadbeef1234deadbeef12345678', $data);

    Cache::spy();

    app(CapeManager::class)->listCapes();

    Cache::shouldHaveReceived('remember')
        ->withArgs(fn ($key, $ttl) => $key === 'capes.list' && $ttl === 86400)
        ->once();
});

it('listCapes includes animated flag — false for 64x32', function () {
    CosmeticAsset::create([
        'sha' => 'deadbeef1234deadbeef1234deadbeef12345678',
        'type' => CosmeticType::TEXTURE,
        'slot' => CosmeticSlot::BACK,
        'status' => CosmeticStatus::APPROVED,
        'width' => 64,
        'height' => 32,
        'visibility' => CosmeticVisibility::PUBLIC,
    ]);

    $result = app(CapeManager::class)->listCapes();

    expect($result[0])->toMatchArray([
        'sha' => 'deadbeef1234deadbeef1234deadbeef12345678',
        'width' => 64,
        'height' => 32,
        'animated' => false,
    ]);
});

it('listCapes includes animated flag — true for 64x64 sprite sheet', function () {
    CosmeticAsset::create([
        'sha' => 'deadbeef1234deadbeef1234deadbeef12345678',
        'type' => CosmeticType::TEXTURE,
        'slot' => CosmeticSlot::BACK,
        'status' => CosmeticStatus::APPROVED,
        'width' => 64,
        'height' => 64,
        'visibility' => CosmeticVisibility::PUBLIC,
    ]);

    $result = app(CapeManager::class)->listCapes();

    expect($result[0]['animated'])->toBeTrue();
});

it('approveCape invalidates capes.list cache', function () {
    config(['image.driver' => 'gd']);
    app()->singleton('image', fn () => new \Intervention\Image\ImageManager(['driver' => 'gd']));

    Storage::fake('queue');
    $png = imagecreatetruecolor(64, 32);
    ob_start();
    imagepng($png);
    $data = ob_get_clean();
    imagedestroy($png);
    Storage::disk('queue')->put('sha123', $data);

    CosmeticAsset::create([
        'sha' => 'sha123',
        'type' => CosmeticType::TEXTURE,
        'slot' => CosmeticSlot::BACK,
        'status' => CosmeticStatus::QUEUED,
        'visibility' => CosmeticVisibility::PUBLIC,
    ]);

    Cache::spy();

    app(CapeManager::class)->approveCape('sha123');

    Cache::shouldHaveReceived('forget')->with('capes.list')->once();
});

it('banCape invalidates capes.list cache', function () {
    Storage::fake('queue');
    Storage::fake('banned');
    Storage::disk('queue')->put('sha123', 'data');

    Cache::spy();

    app(CapeManager::class)->banCape('sha123');

    Cache::shouldHaveReceived('forget')->with('capes.list')->once();
});
