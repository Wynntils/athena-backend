<?php

use App\Http\Middleware\BlockNonWynntilsUserAgents;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(Tests\TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    $this->withoutMiddleware(BlockNonWynntilsUserAgents::class);
});

it('returns an ETag header with a successful user info response', function () {
    $user = User::factory()->create();

    $this->postJson('/user/getInfo', ['uuid' => $user->id])
        ->assertOk()
        ->assertHeader('ETag');
});

it('returns 304 when If-None-Match matches the current ETag', function () {
    $user = User::factory()->create();

    $etag = '"'.md5($user->account_type->value.json_encode($user->cosmetic_info)).'"';

    $this->withHeaders(['If-None-Match' => $etag])
        ->postJson('/user/getInfo', ['uuid' => $user->id])
        ->assertStatus(304);
});

it('returns 200 with ETag header when If-None-Match is stale', function () {
    $user = User::factory()->create();

    $this->withHeaders(['If-None-Match' => 'outdated-etag'])
        ->postJson('/user/getInfo', ['uuid' => $user->id])
        ->assertOk()
        ->assertHeader('ETag');
});

it('returns 200 with ETag header when If-None-Match is absent', function () {
    $user = User::factory()->create();

    $this->postJson('/user/getInfo', ['uuid' => $user->id])
        ->assertOk()
        ->assertHeader('ETag');
});

it('returns a new ETag after cosmetic_info changes', function () {
    $user = User::factory()->create(['cosmetic_info' => ['capeTexture' => 'abc']]);

    $first = $this->postJson('/user/getInfo', ['uuid' => $user->id]);
    $firstEtag = $first->headers->get('ETag');

    $user->cosmetic_info = ['capeTexture' => 'xyz'];
    $user->save();

    $second = $this->postJson('/user/getInfo', ['uuid' => $user->id]);
    $secondEtag = $second->headers->get('ETag');

    expect($firstEtag)->not->toBe($secondEtag);
});
