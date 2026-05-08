<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(Tests\TestCase::class, RefreshDatabase::class);

it('byDiscordId scope returns users matching discord id', function () {
    User::factory()->create([
        'discord_info' => ['id' => '123456789', 'username' => 'testuser'],
    ]);
    User::factory()->create([
        'discord_info' => ['id' => '999999999', 'username' => 'other'],
    ]);

    $result = User::byDiscordId('123456789')->get();

    expect($result)->toHaveCount(1)
        ->and($result->first()->discord_info['id'])->toBe('123456789');
});

it('byDiscordId scope returns empty when no match', function () {
    User::factory()->create([
        'discord_info' => ['id' => '123456789', 'username' => 'testuser'],
    ]);

    $result = User::byDiscordId('000000000')->get();

    expect($result)->toHaveCount(0);
});

it('byCapeTexture scope returns users matching cape texture', function () {
    User::factory()->create([
        'cosmetic_info' => ['capeTexture' => 'abc123sha'],
    ]);
    User::factory()->create([
        'cosmetic_info' => ['capeTexture' => 'different'],
    ]);

    $result = User::byCapeTexture('abc123sha')->get();

    expect($result)->toHaveCount(1)
        ->and($result->first()->cosmetic_info['capeTexture'])->toBe('abc123sha');
});

it('byCapeTexture scope returns empty when no match', function () {
    User::factory()->create([
        'cosmetic_info' => ['capeTexture' => 'abc123sha'],
    ]);

    $result = User::byCapeTexture('wrongsha')->get();

    expect($result)->toHaveCount(0);
});
