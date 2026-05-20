<?php

use App\Enums\CosmeticSlot;
use App\Enums\CosmeticStatus;
use App\Enums\CosmeticType;
use App\Enums\CosmeticVisibility;
use App\Models\CosmeticAsset;
use App\Models\CosmeticVote;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(Tests\TestCase::class, RefreshDatabase::class);

it('creates a cosmetic asset with all columns', function () {
    $user = User::factory()->create();

    $asset = CosmeticAsset::create([
        'sha' => str_repeat('a', 40),
        'type' => CosmeticType::TEXTURE,
        'slot' => CosmeticSlot::BACK,
        'status' => CosmeticStatus::QUEUED,
        'uploader_id' => $user->id,
        'name' => 'My Cape',
        'visibility' => CosmeticVisibility::PUBLIC,
        'tags' => ['animated'],
        'uploaded_at' => now(),
    ]);

    expect($asset->sha)->toBe(str_repeat('a', 40))
        ->and($asset->type)->toBe(CosmeticType::TEXTURE)
        ->and($asset->slot)->toBe(CosmeticSlot::BACK)
        ->and($asset->status)->toBe(CosmeticStatus::QUEUED)
        ->and($asset->equip_count)->toBe(0)
        ->and($asset->tags)->toBe(['animated']);
});

it('resolves uploader relationship', function () {
    $user = User::factory()->create();
    $asset = CosmeticAsset::factory()->create(['uploader_id' => $user->id]);

    expect($asset->uploader->id)->toBe($user->id);
});

it('resolves votes relationship', function () {
    $user = User::factory()->create();
    $asset = CosmeticAsset::factory()->create();
    CosmeticVote::create(['cosmetic_id' => $asset->id, 'user_id' => $user->id, 'vote' => 1]);

    expect($asset->refresh()->votes)->toHaveCount(1);
});

it('enforces unique vote per user per asset', function () {
    $user = User::factory()->create();
    $asset = CosmeticAsset::factory()->create();

    CosmeticVote::create(['cosmetic_id' => $asset->id, 'user_id' => $user->id, 'vote' => 1]);

    expect(fn () => CosmeticVote::create(['cosmetic_id' => $asset->id, 'user_id' => $user->id, 'vote' => -1]))
        ->toThrow(\Illuminate\Database\QueryException::class);
});

it('scopes approved public assets', function () {
    CosmeticAsset::factory()->create(['status' => CosmeticStatus::APPROVED, 'visibility' => CosmeticVisibility::PUBLIC]);
    CosmeticAsset::factory()->create(['status' => CosmeticStatus::QUEUED, 'visibility' => CosmeticVisibility::PUBLIC]);
    CosmeticAsset::factory()->create(['status' => CosmeticStatus::APPROVED, 'visibility' => CosmeticVisibility::PRIVATE]);

    expect(CosmeticAsset::approvedPublic()->count())->toBe(1);
});
