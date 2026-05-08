<?php

use App\Http\Middleware\AthenaTokenMiddleware;
use App\Models\Guild;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(Tests\TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    $this->withoutMiddleware(AthenaTokenMiddleware::class);
});

it('rejects invalid hex color', function () {
    Guild::create(['id' => 'TestGuild', 'prefix' => 'TG', 'color' => '#ffffff']);

    $response = $this->postJson('/guilds/setColor/testkey', [
        'guild' => 'TestGuild',
        'color' => 'notacolor',
    ]);

    $response->assertUnprocessable();
});

it('rejects non-existent guild', function () {
    $response = $this->postJson('/guilds/setColor/testkey', [
        'guild' => 'DoesNotExist',
        'color' => '#aabbcc',
    ]);

    $response->assertUnprocessable();
});

it('accepts valid guild and hex color', function () {
    Guild::create(['id' => 'TestGuild', 'prefix' => 'TG', 'color' => '#ffffff']);

    $response = $this->postJson('/guilds/setColor/testkey', [
        'guild' => 'TestGuild',
        'color' => '#aabbcc',
    ]);

    $response->assertOk();
    expect(Guild::find('TestGuild')->color)->toBe('#aabbcc');
});
