<?php

use App\Models\CosmeticAsset;
use App\Models\CosmeticVote;
use App\Models\User;
use App\Services\CosmeticAssetService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(Tests\TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    $this->service = app(CosmeticAssetService::class);
});

it('records an upvote', function () {
    $user = User::factory()->create();
    $asset = CosmeticAsset::factory()->approved()->create();

    $this->service->vote($user, $asset->sha, 1);

    expect(CosmeticVote::where('cosmetic_id', $asset->id)->where('user_id', $user->id)->value('vote'))->toBe(1);
});

it('updates an existing vote', function () {
    $user = User::factory()->create();
    $asset = CosmeticAsset::factory()->approved()->create();
    CosmeticVote::create(['cosmetic_id' => $asset->id, 'user_id' => $user->id, 'vote' => 1]);

    $this->service->vote($user, $asset->sha, -1);

    expect(CosmeticVote::where('cosmetic_id', $asset->id)->where('user_id', $user->id)->value('vote'))->toBe(-1);
});

it('blocks self-vote', function () {
    $uploader = User::factory()->create();
    $asset = CosmeticAsset::factory()->approved()->create(['uploader_id' => $uploader->id]);

    expect(fn () => $this->service->vote($uploader, $asset->sha, 1))
        ->toThrow(\InvalidArgumentException::class, 'Cannot vote on your own cosmetic');
});

it('removes a vote', function () {
    $user = User::factory()->create();
    $asset = CosmeticAsset::factory()->approved()->create();
    CosmeticVote::create(['cosmetic_id' => $asset->id, 'user_id' => $user->id, 'vote' => 1]);

    $this->service->unvote($user, $asset->sha);

    expect(CosmeticVote::where('cosmetic_id', $asset->id)->where('user_id', $user->id)->exists())->toBeFalse();
});

it('submits an edit to pending fields', function () {
    $user = User::factory()->create();
    $asset = CosmeticAsset::factory()->approved()->create(['uploader_id' => $user->id]);

    $this->service->submitEdit($user, $asset->sha, ['name' => 'New Name', 'visibility' => 'private']);

    $asset->refresh();
    expect($asset->pending_name)->toBe('New Name')
        ->and($asset->pending_visibility->value)->toBe('private')
        ->and($asset->name)->toBeNull();
});

it('throws when a pending edit already exists', function () {
    $user = User::factory()->create();
    $asset = CosmeticAsset::factory()->approved()->create([
        'uploader_id' => $user->id,
        'pending_name' => 'Old pending',
    ]);

    expect(fn () => $this->service->submitEdit($user, $asset->sha, ['name' => 'New']))
        ->toThrow(\RuntimeException::class, 'A pending edit already exists');
});

it('throws when non-uploader submits an edit', function () {
    $user = User::factory()->create();
    $other = User::factory()->create();
    $asset = CosmeticAsset::factory()->approved()->create(['uploader_id' => $other->id]);

    expect(fn () => $this->service->submitEdit($user, $asset->sha, ['name' => 'Hacked']))
        ->toThrow(\InvalidArgumentException::class, 'Only the uploader can edit this cosmetic');
});

it('approveEdit flushes pending fields to live fields', function () {
    $asset = CosmeticAsset::factory()->approved()->create([
        'name' => 'Old Name',
        'pending_name' => 'New Name',
        'pending_visibility' => 'private',
        'pending_tags' => ['guild:artisans'],
    ]);

    $this->service->approveEdit($asset->sha);

    $asset->refresh();
    expect($asset->name)->toBe('New Name')
        ->and($asset->visibility->value)->toBe('private')
        ->and($asset->pending_name)->toBeNull()
        ->and($asset->pending_visibility)->toBeNull();
});

it('rejectEdit clears pending fields', function () {
    $asset = CosmeticAsset::factory()->approved()->create(['pending_name' => 'Pending']);

    $this->service->rejectEdit($asset->sha);

    expect($asset->fresh()->pending_name)->toBeNull();
});

it('increments equip count atomically', function () {
    $asset = CosmeticAsset::factory()->approved()->create(['equip_count' => 5]);

    $this->service->incrementEquipCount($asset->sha);

    expect($asset->fresh()->equip_count)->toBe(6);
});

it('decrements equip count and does not go below zero', function () {
    $asset = CosmeticAsset::factory()->approved()->create(['equip_count' => 0]);

    $this->service->decrementEquipCount($asset->sha);

    expect($asset->fresh()->equip_count)->toBe(0);
});
