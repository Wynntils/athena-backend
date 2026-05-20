<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;

uses(Tests\TestCase::class, RefreshDatabase::class);

it('returns callback view with token on successful discord login', function () {
    $discordUser = new SocialiteUser;
    $discordUser->id = '123456789';
    $discordUser->token = 'fake-token';
    $discordUser->refreshToken = null;
    $discordUser->expiresIn = null;

    $user = User::factory()->create([
        'discord_info' => ['id' => '123456789', 'username' => 'TestUser'],
    ]);

    Socialite::shouldReceive('driver->user')->andReturn($discordUser);

    $response = $this->get('/oauth/discord/callback');

    $response->assertOk()
        ->assertViewIs('auth.oauth-callback')
        ->assertViewHas('token', $user->auth_token);

    $this->assertAuthenticated();
});

it('returns callback view with message when no discord account linked', function () {
    $discordUser = new SocialiteUser;
    $discordUser->id = '999999999';
    $discordUser->token = 'fake-token';
    $discordUser->refreshToken = null;
    $discordUser->expiresIn = null;

    Socialite::shouldReceive('driver->user')->andReturn($discordUser);

    $response = $this->get('/oauth/discord/callback');

    $response->assertOk()
        ->assertViewIs('auth.oauth-callback')
        ->assertViewHas('message', 'No Wynntils account is linked to this Discord account.');
});

it('returns callback view with message on provider error query param', function () {
    $response = $this->get('/oauth/discord/callback?error=access_denied');

    $response->assertOk()
        ->assertViewIs('auth.oauth-callback')
        ->assertViewHas('message', 'access_denied');
});

it('view contains success postMessage JS when token is present', function () {
    $response = $this->view('auth.oauth-callback', ['token' => 'test-token-uuid']);

    $response->assertSee("type: 'wynntils_oauth_callback'", false)
        ->assertSee('success: true', false)
        ->assertSee('"test-token-uuid"', false);
});

it('view contains error postMessage JS when message is present', function () {
    $response = $this->view('auth.oauth-callback', ['message' => 'No account linked.']);

    $response->assertSee("type: 'wynntils_oauth_callback'", false)
        ->assertSee('success: false', false)
        ->assertSee('No account linked.', false);
});

it('returns callback view with message when no minecraft account linked', function () {
    $minecraftUser = new SocialiteUser;
    $minecraftUser->uuid = '00000000-0000-0000-0000-000000000000';
    $minecraftUser->token = 'fake-token';
    $minecraftUser->refreshToken = null;
    $minecraftUser->expiresIn = null;

    Socialite::shouldReceive('driver->user')->andReturn($minecraftUser);

    $response = $this->get('/oauth/minecraft/callback');

    $response->assertOk()
        ->assertViewIs('auth.oauth-callback')
        ->assertViewHas('message', 'No Wynntils account is linked to this Minecraft account.');
});
