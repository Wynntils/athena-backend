<?php

use App\Enums\AccountType;
use App\Models\User;
use App\Models\VoiceBan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

uses(Tests\TestCase::class, RefreshDatabase::class);

function banStaff(): User
{
    return User::factory()->create(['account_type' => AccountType::MODERATOR]);
}

it('lists bans with status badges', function () {
    $now = now()->getTimestampMs();
    $active = VoiceBan::factory()->create();
    $expired = VoiceBan::factory()->create(['expires_at' => $now - 1000]);
    $lifted = VoiceBan::factory()->create(['lifted_at' => $now]);

    $this->actingAs(banStaff())->get('/voice-bans')
        ->assertOk()
        ->assertSee('data-ban="'.$active->id.'" data-status="active"', false)
        ->assertSee('data-ban="'.$expired->id.'" data-status="expired"', false)
        ->assertSee('data-ban="'.$lifted->id.'" data-status="lifted"', false);
});

it('prefills the create form from the query string', function () {
    $target = User::factory()->create(['username' => 'Victim']);

    $this->actingAs(banStaff())->get('/voice-bans/create?user='.$target->id)
        ->assertOk()->assertSee($target->id)->assertSee('Victim');
});

it('creates a permanent ban', function () {
    $mod = banStaff();
    $target = Str::uuid()->toString();

    $this->actingAs($mod)->post('/voice-bans', ['user_id' => $target, 'reason' => 'slurs', 'duration' => 'permanent'])
        ->assertRedirect(route('voice.bans.index'));

    $ban = VoiceBan::where('user_id', $target)->firstOrFail();
    expect($ban->expires_at)->toBeNull();
    expect($ban->lifted_at)->toBeNull();
    expect($ban->banned_by)->toBe($mod->username);
    expect($ban->isActive(now()->getTimestampMs()))->toBeTrue();
});

it('creates a timed ban', function () {
    $target = Str::uuid()->toString();
    $this->actingAs(banStaff())->post('/voice-bans', ['user_id' => $target, 'reason' => 'r', 'duration' => '7d']);

    $ban = VoiceBan::where('user_id', $target)->firstOrFail();
    $expected = now()->getTimestampMs() + 7 * 24 * 60 * 60 * 1000;
    expect($ban->expires_at)->toBeGreaterThan($expected - 5000)->toBeLessThan($expected + 5000);
});

it('rejects an unknown duration', function () {
    $this->actingAs(banStaff())->post('/voice-bans', ['user_id' => Str::uuid()->toString(), 'reason' => 'r', 'duration' => 'forever-ish'])
        ->assertSessionHasErrors('duration');
    expect(VoiceBan::count())->toBe(0);
});

it('rejects a non-uuid user id', function () {
    $this->actingAs(banStaff())->post('/voice-bans', ['user_id' => 'Bob', 'reason' => 'r', 'duration' => '1d'])
        ->assertSessionHasErrors('user_id');
});

it('lifts a ban', function () {
    $ban = VoiceBan::factory()->create();
    $this->actingAs(banStaff())->put("/voice-bans/{$ban->id}/lift")->assertRedirect(route('voice.bans.index'));
    expect($ban->refresh()->lifted_at)->not->toBeNull();
});

it('rejects non-staff', function () {
    $this->actingAs(User::factory()->create(['account_type' => AccountType::NORMAL]))
        ->post('/voice-bans', ['user_id' => Str::uuid()->toString(), 'reason' => 'r', 'duration' => '1d'])
        ->assertSee('do not have the required permissions');
    expect(VoiceBan::count())->toBe(0);
});
