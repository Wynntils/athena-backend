<?php

use App\Enums\AccountType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;

uses(Tests\TestCase::class, RefreshDatabase::class);

it('clears the user model cache when account_type changes', function () {
    $user = User::factory()->create();
    Cache::put("user-{$user->id}", $user, 3600);

    $user->account_type = AccountType::DONATOR;
    $user->save();

    expect(Cache::has("user-{$user->id}"))->toBeFalse();
});

it('clears the user model cache when cosmetic_info changes', function () {
    $user = User::factory()->create(['cosmetic_info' => ['capeTexture' => 'abc123']]);
    Cache::put("user-{$user->id}", $user, 3600);

    $user->cosmetic_info = ['capeTexture' => 'def456'];
    $user->save();

    expect(Cache::has("user-{$user->id}"))->toBeFalse();
});

it('clears the old cape texture cache keys when cosmetic_info changes', function () {
    $oldTexture = 'abc123';
    $user = User::factory()->create(['cosmetic_info' => ['capeTexture' => $oldTexture]]);
    Cache::put("cape-texture-{$oldTexture}-1", 'base64data', 2592000);
    Cache::put("cape-texture-{$oldTexture}-0", 'base64data', 2592000);

    $user->cosmetic_info = ['capeTexture' => 'def456'];
    $user->save();

    expect(Cache::has("cape-texture-{$oldTexture}-1"))->toBeFalse()
        ->and(Cache::has("cape-texture-{$oldTexture}-0"))->toBeFalse();
});

it('does not clear caches when unrelated fields change', function () {
    $user = User::factory()->create();
    Cache::put("user-{$user->id}", $user, 3600);

    $user->username = 'new_username';
    $user->save();

    expect(Cache::has("user-{$user->id}"))->toBeTrue();
});

it('does not clear cape texture cache when only account_type changes', function () {
    $texture = 'abc123';
    $user = User::factory()->create(['cosmetic_info' => ['capeTexture' => $texture]]);
    Cache::put("cape-texture-{$texture}-1", 'base64data', 2592000);

    $user->account_type = AccountType::DONATOR;
    $user->save();

    expect(Cache::has("cape-texture-{$texture}-1"))->toBeTrue();
});
