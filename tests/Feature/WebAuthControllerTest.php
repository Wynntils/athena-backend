<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(Tests\TestCase::class, RefreshDatabase::class);

it('returns token and user on valid credentials', function () {
    $user = User::factory()->create([
        'username' => 'TestPlayer',
        'password' => \Hash::make('secret123'),
    ]);

    $response = $this->postJson('/auth/login', [
        'username' => 'TestPlayer',
        'password' => 'secret123',
    ]);

    $response->assertOk()
        ->assertJsonStructure([
            'token',
            'user' => ['uuid', 'username', 'accountType', 'discord'],
        ])
        ->assertJsonPath('user.username', 'TestPlayer')
        ->assertJsonPath('user.uuid', $user->id);
});

it('returns 401 on wrong password', function () {
    User::factory()->create([
        'username' => 'TestPlayer',
        'password' => \Hash::make('secret123'),
    ]);

    $response = $this->postJson('/auth/login', [
        'username' => 'TestPlayer',
        'password' => 'wrongpassword',
    ]);

    $response->assertUnauthorized()
        ->assertJsonPath('message', 'Invalid username or password.');
});

it('returns 401 for unknown username', function () {
    $response = $this->postJson('/auth/login', [
        'username' => 'NoSuchPlayer',
        'password' => 'anything',
    ]);

    $response->assertUnauthorized()
        ->assertJsonPath('message', 'Invalid username or password.');
});

it('returns 422 when all fields are missing', function () {
    $response = $this->postJson('/auth/login', []);
    $response->assertUnprocessable();
});

it('returns 422 when password is missing', function () {
    $response = $this->postJson('/auth/login', ['username' => 'TestPlayer']);
    $response->assertUnprocessable();
});

it('returns 422 when username is missing', function () {
    $response = $this->postJson('/auth/login', ['password' => 'secret123']);
    $response->assertUnprocessable();
});

it('accepts the remember field without error', function () {
    $user = User::factory()->create([
        'username' => 'TestPlayer',
        'password' => \Hash::make('secret123'),
    ]);

    $response = $this->postJson('/auth/login', [
        'username' => 'TestPlayer',
        'password' => 'secret123',
        'remember' => true,
    ]);

    $response->assertOk();
});
