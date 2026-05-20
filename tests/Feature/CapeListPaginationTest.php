<?php

use App\Models\CosmeticAsset;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;

uses(Tests\TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    Cache::forget('capes.list');

    // Seed 120 approved BACK TEXTURE assets into the DB
    foreach (range(1, 120) as $i) {
        CosmeticAsset::factory()->approved()->create([
            'sha' => str_pad((string) $i, 40, '0', STR_PAD_LEFT),
            'width' => 64,
            'height' => 32,
        ]);
    }
});

it('returns paginated data with defaults', function () {
    $this->getJson('/capes/list')
        ->assertOk()
        ->assertJsonStructure(['data', 'total', 'page', 'per_page', 'last_page'])
        ->assertJsonPath('total', 120)
        ->assertJsonPath('page', 1)
        ->assertJsonPath('per_page', 50)
        ->assertJsonPath('last_page', 3)
        ->assertJsonCount(50, 'data');
});

it('respects page param', function () {
    $this->getJson('/capes/list?page=3')
        ->assertOk()
        ->assertJsonPath('page', 3)
        ->assertJsonCount(20, 'data');
});

it('caps per_page at 100', function () {
    $this->getJson('/capes/list?per_page=200')
        ->assertOk()
        ->assertJsonPath('per_page', 100)
        ->assertJsonPath('last_page', 2);  // 120 items / 100 per_page = ceil = 2
});

it('returns correct slice on page 2', function () {
    $response = $this->getJson('/capes/list?page=2&per_page=50');
    $response->assertOk()
        ->assertJsonPath('page', 2)
        ->assertJsonCount(50, 'data');
    // The 51st cape (index 50 from seed) has sha padded '51'
    expect($response->json('data.0.sha'))->toBe(str_pad('51', 40, '0', STR_PAD_LEFT));
});

it('returns ETag header', function () {
    $this->getJson('/capes/list')->assertHeader('ETag');
});

it('returns 304 when ETag matches', function () {
    $response = $this->getJson('/capes/list');
    $etag = $response->headers->get('ETag');

    $r304 = $this->withHeaders(['If-None-Match' => $etag])
        ->get('/capes/list');
    $r304->assertStatus(304);
    expect($r304->getContent())->toBe('');
});
