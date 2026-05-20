<?php

use App\Models\CosmeticAsset;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(Tests\TestCase::class, RefreshDatabase::class);

function validToken(): string
{
    return app(\App\Http\Libraries\CapeManager::class)->getToken();
}

// ──────────────────────────────────────────────────────────────────────────────
// approve-edit
// ──────────────────────────────────────────────────────────────────────────────

it('approve-edit returns 401 with invalid token', function () {
    $asset = CosmeticAsset::factory()->approved()->create([
        'pending_name' => 'New Name',
    ]);

    $this->getJson("/capes/queue/approve-edit/bad-token/{$asset->sha}")
        ->assertStatus(401);
});

it('approve-edit returns 404 when sha not found', function () {
    $token = validToken();

    $this->getJson("/capes/queue/approve-edit/{$token}/0000000000000000000000000000000000000000")
        ->assertStatus(404);
});

it('approve-edit returns 404 when no pending edit exists', function () {
    $token = validToken();
    $asset = CosmeticAsset::factory()->approved()->create();

    $this->getJson("/capes/queue/approve-edit/{$token}/{$asset->sha}")
        ->assertStatus(404)
        ->assertJsonPath('message', 'No pending edit exists.');
});

it('approve-edit flushes pending fields to live fields', function () {
    $token = validToken();
    $asset = CosmeticAsset::factory()->approved()->create([
        'pending_name' => 'Approved Name',
    ]);

    $this->getJson("/capes/queue/approve-edit/{$token}/{$asset->sha}")
        ->assertOk()
        ->assertJsonPath('message', 'Edit approved.');

    $asset->refresh();
    expect($asset->name)->toBe('Approved Name');
    expect($asset->pending_name)->toBeNull();
});

// ──────────────────────────────────────────────────────────────────────────────
// reject-edit
// ──────────────────────────────────────────────────────────────────────────────

it('reject-edit returns 401 with invalid token', function () {
    $asset = CosmeticAsset::factory()->approved()->create([
        'pending_name' => 'Some Name',
    ]);

    $this->getJson("/capes/reject-edit/bad-token/{$asset->sha}")
        ->assertStatus(401);
});

it('reject-edit returns 404 when sha not found', function () {
    $token = validToken();

    $this->getJson("/capes/reject-edit/{$token}/0000000000000000000000000000000000000000")
        ->assertStatus(404);
});

it('reject-edit clears pending fields', function () {
    $token = validToken();
    $asset = CosmeticAsset::factory()->approved()->create([
        'pending_name' => 'Rejected Name',
    ]);

    $this->getJson("/capes/reject-edit/{$token}/{$asset->sha}")
        ->assertOk()
        ->assertJsonPath('message', 'Edit rejected.');

    $asset->refresh();
    expect($asset->pending_name)->toBeNull();
    expect($asset->name)->toBeNull();
});

it('reject-edit works even when no pending edit exists', function () {
    $token = validToken();
    $asset = CosmeticAsset::factory()->approved()->create();

    $this->getJson("/capes/reject-edit/{$token}/{$asset->sha}")
        ->assertOk()
        ->assertJsonPath('message', 'Edit rejected.');
});
