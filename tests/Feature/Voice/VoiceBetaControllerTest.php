<?php

use App\Enums\AccountType;
use App\Models\User;
use App\Models\VoiceBeta;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

uses(Tests\TestCase::class, RefreshDatabase::class);

function betaStaff(): User
{
    return User::factory()->create(['account_type' => AccountType::MODERATOR]);
}

it('lists beta users with resolved usernames', function () {
    $user = User::factory()->create(['username' => 'Tester']);
    VoiceBeta::create(['user_id' => $user->id]);
    $orphan = Str::uuid()->toString();
    VoiceBeta::create(['user_id' => $orphan]);

    $this->actingAs(betaStaff())->get('/voice-beta')
        ->assertOk()->assertSee('Tester')->assertSee($orphan)->assertSee('(unknown)');
});

it('adds a beta user by username', function () {
    $user = User::factory()->create(['username' => 'Tester']);
    $this->actingAs(betaStaff())->post('/voice-beta', ['identifier' => 'Tester'])->assertRedirect(route('voice.beta.index'));
    expect(VoiceBeta::whereKey($user->id)->exists())->toBeTrue();
});

it('adds a beta user by uuid', function () {
    $user = User::factory()->create();
    $this->actingAs(betaStaff())->post('/voice-beta', ['identifier' => $user->id])->assertRedirect();
    expect(VoiceBeta::whereKey($user->id)->exists())->toBeTrue();
});

it('is idempotent when adding twice', function () {
    $user = User::factory()->create(['username' => 'Tester']);
    $this->actingAs(betaStaff())->post('/voice-beta', ['identifier' => 'Tester']);
    $this->actingAs(betaStaff())->post('/voice-beta', ['identifier' => $user->id])->assertRedirect();
    expect(VoiceBeta::count())->toBe(1);
});

it('rejects an unknown identifier', function () {
    $this->actingAs(betaStaff())->post('/voice-beta', ['identifier' => 'Ghost'])->assertSessionHasErrors('identifier');
    $this->actingAs(betaStaff())->post('/voice-beta', ['identifier' => Str::uuid()->toString()])->assertSessionHasErrors('identifier');
    expect(VoiceBeta::count())->toBe(0);
});

it('requires an identifier', function () {
    $this->actingAs(betaStaff())->post('/voice-beta', [])->assertSessionHasErrors('identifier');
});

it('removes a beta user', function () {
    $user = User::factory()->create();
    VoiceBeta::create(['user_id' => $user->id]);
    $this->actingAs(betaStaff())->delete("/voice-beta/{$user->id}")->assertRedirect(route('voice.beta.index'));
    expect(VoiceBeta::whereKey($user->id)->exists())->toBeFalse();
});

it('rejects non-staff', function () {
    $this->actingAs(User::factory()->create(['account_type' => AccountType::HELPER]))->get('/voice-beta')->assertOk();
    $this->actingAs(User::factory()->create(['account_type' => AccountType::NORMAL]))->get('/voice-beta')
        ->assertSee('do not have the required permissions');
});
