<?php

use App\Models\VoiceBan;
use App\Models\VoiceBeta;
use App\Models\VoiceReport;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

uses(Tests\TestCase::class, RefreshDatabase::class);

it('reads a report row with millis and witness helpers', function () {
    $report = VoiceReport::create([
        'reporter_id' => Str::uuid()->toString(),
        'target_id' => Str::uuid()->toString(),
        'reason' => 'spam',
        'world' => 'EU22',
        'instance' => '',
        'witness_ids' => 'aaaaaaaa-0000-0000-0000-000000000000,bbbbbbbb-0000-0000-0000-000000000000',
        'created_at' => 1_700_000_000_000,
        'handled' => false,
        'target_audio_path' => '/app/voice-reports/1/target.opus',
    ]);

    expect($report->handled)->toBeFalse();
    expect($report->createdAtCarbon()->timestamp)->toBe(1_700_000_000);
    expect($report->witnessIdArray())->toHaveCount(2);
    expect($report->hasTargetAudio())->toBeTrue();
    expect($report->hasReporterAudio())->toBeFalse();
});

it('computes active bans from lifted/expiry', function () {
    $now = 1_000_000;
    $active = VoiceBan::create(['user_id' => Str::uuid()->toString(), 'reason' => 'r', 'banned_by' => 's', 'banned_at' => $now, 'expires_at' => $now + 10]);
    $expired = VoiceBan::create(['user_id' => Str::uuid()->toString(), 'reason' => 'r', 'banned_by' => 's', 'banned_at' => $now, 'expires_at' => $now - 1]);
    $lifted = VoiceBan::create(['user_id' => Str::uuid()->toString(), 'reason' => 'r', 'banned_by' => 's', 'banned_at' => $now, 'lifted_at' => $now]);
    $permanent = VoiceBan::create(['user_id' => Str::uuid()->toString(), 'reason' => 'r', 'banned_by' => 's', 'banned_at' => $now]);

    expect($active->isActive($now))->toBeTrue();
    expect($expired->isActive($now))->toBeFalse();
    expect($lifted->isActive($now))->toBeFalse();
    expect($permanent->isActive($now))->toBeTrue();
    expect(VoiceBan::active($now)->pluck('id')->all())->toEqualCanonicalizing([$active->id, $permanent->id]);
});

it('stores beta rows keyed by user_id', function () {
    $id = Str::uuid()->toString();
    VoiceBeta::create(['user_id' => $id]);
    expect(VoiceBeta::whereKey($id)->exists())->toBeTrue();
});
