<?php

use App\Enums\AccountType;
use App\Models\User;
use App\Models\VoiceReport;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(Tests\TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    config(['athena.voice.report_dir' => sys_get_temp_dir().'/athena-voice-'.uniqid()]);
});

function staff(): User
{
    return User::factory()->create(['account_type' => AccountType::MODERATOR]);
}

it('lists unhandled reports by default for staff', function () {
    $unhandled = VoiceReport::factory()->create(['handled' => false]);
    $handled = VoiceReport::factory()->create(['handled' => true]);

    $this->actingAs(staff())->get('/voice-reports')
        ->assertOk()
        ->assertSee('data-report="'.$unhandled->id.'"', false)
        ->assertDontSee('data-report="'.$handled->id.'"', false);
});

it('lists handled reports when asked', function () {
    $handled = VoiceReport::factory()->create(['handled' => true]);

    $this->actingAs(staff())->get('/voice-reports?showHandled=on')
        ->assertOk()
        ->assertSee('data-report="'.$handled->id.'"', false);
});

it('searches by reason', function () {
    $match = VoiceReport::factory()->create(['reason' => 'unique-needle']);
    $other = VoiceReport::factory()->create(['reason' => 'nothing']);

    $this->actingAs(staff())->get('/voice-reports?search=needle')
        ->assertSee('data-report="'.$match->id.'"', false)
        ->assertDontSee('data-report="'.$other->id.'"', false);
});

it('rejects non-staff', function () {
    $this->actingAs(User::factory()->create(['account_type' => AccountType::NORMAL]))
        ->get('/voice-reports')
        ->assertSee('do not have the required permissions');
});

it('rejects guests', function () {
    $this->get('/voice-reports')->assertUnauthorized();
});

it('shows a report with resolved usernames', function () {
    $reporter = User::factory()->create(['username' => 'Alice']);
    $target = User::factory()->create(['username' => 'Bob']);
    $witness = User::factory()->create(['username' => 'Carol']);
    $report = VoiceReport::factory()->create([
        'reporter_id' => $reporter->id,
        'target_id' => $target->id,
        'witness_ids' => $witness->id.',00000000-0000-0000-0000-000000000000',
        'world' => 'EU22',
        'reason' => 'slurs',
        'target_audio_path' => 'x',
    ]);

    $this->actingAs(staff())->get("/voice-reports/{$report->id}")
        ->assertOk()
        ->assertSee('Alice')->assertSee('Bob')->assertSee('Carol')
        ->assertSee('00000000-0000-0000-0000-000000000000')
        ->assertSee('EU22')->assertSee('slurs')
        ->assertSee(route('voice.audio', ['voiceReport' => $report, 'side' => 'target']), false)
        ->assertDontSee(route('voice.audio', ['voiceReport' => $report, 'side' => 'reporter']), false);
});

it('marks a report handled and unhandled', function () {
    $mod = staff();
    $report = VoiceReport::factory()->create(['handled' => false]);

    $this->actingAs($mod)->putJson("/voice-reports/{$report->id}/handled", ['handled' => 'true'])
        ->assertOk()->assertJson(['success' => true]);

    $report->refresh();
    expect($report->handled)->toBeTrue();
    expect($report->handled_by)->toBe($mod->username);
    expect($report->handled_at)->not->toBeNull();

    $this->actingAs($mod)->putJson("/voice-reports/{$report->id}/handled", ['handled' => 'false'])->assertOk();

    $report->refresh();
    expect($report->handled)->toBeFalse();
    expect($report->handled_by)->toBeNull();
    expect($report->handled_at)->toBeNull();
});

it('streams audio and 404s when absent', function () {
    $dir = config('athena.voice.report_dir');
    $report = VoiceReport::factory()->create(['target_audio_path' => 'x']);
    mkdir("$dir/{$report->id}", 0777, true);
    file_put_contents("$dir/{$report->id}/target.opus", 'OggS-fake');

    $this->actingAs(staff())->get("/voice-reports/{$report->id}/audio/target")
        ->assertOk()->assertHeader('content-type', 'audio/ogg');
    $this->actingAs(staff())->get("/voice-reports/{$report->id}/audio/reporter")
        ->assertNotFound();

    unlink("$dir/{$report->id}/target.opus");
    rmdir("$dir/{$report->id}");
    rmdir($dir);
});

it('404s when the audio file is missing on disk', function () {
    $report = VoiceReport::factory()->create(['target_audio_path' => 'x']);
    $this->actingAs(staff())->get("/voice-reports/{$report->id}/audio/target")->assertNotFound();
});

it('rejects an invalid audio side', function () {
    $report = VoiceReport::factory()->create(['target_audio_path' => 'x']);
    $this->actingAs(staff())->get("/voice-reports/{$report->id}/audio/bogus")->assertNotFound();
    $this->actingAs(staff())->get("/voice-reports/{$report->id}/audio/..%2F..%2Fetc%2Fpasswd")->assertNotFound();
});

it('adds and deletes sanitized comments', function () {
    $mod = staff();
    $report = VoiceReport::factory()->create();

    $res = $this->actingAs($mod)->putJson("/voice-reports/{$report->id}/comment", [
        'comment' => '<b onclick="evil()">hi</b><script>alert(1)</script>',
    ])->assertOk()->assertJson(['success' => true]);

    $comment = $res->json('comment');
    expect($comment['comment'])->toBe('<b>hi</b>alert(1)');
    expect($comment['user'])->toBe($mod->username);
    expect($report->refresh()->comments)->toHaveCount(1);

    $this->actingAs($mod)->deleteJson("/voice-reports/{$report->id}/comment", ['commentId' => $comment['id']])
        ->assertOk()->assertJson(['success' => true, 'comments' => []]);
    expect($report->refresh()->comments)->toBe([]);
});

it('requires a comment body', function () {
    $report = VoiceReport::factory()->create();
    $this->actingAs(staff())->putJson("/voice-reports/{$report->id}/comment", [])->assertUnprocessable();
});
