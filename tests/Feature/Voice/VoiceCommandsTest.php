<?php

use App\Models\VoiceReport;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;

uses(Tests\TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    config(['athena.voice.report_dir' => sys_get_temp_dir().'/athena-voice-'.uniqid()]);
    config(['athena.webhook.discord.webhook.voice' => null]);
});

afterEach(function () {
    File::deleteDirectory(config('athena.voice.report_dir'));
});

it('stamps discord_notified_at once', function () {
    $report = VoiceReport::factory()->create(['discord_notified_at' => null]);
    $already = VoiceReport::factory()->create(['discord_notified_at' => 123]);

    $this->artisan('voice:notify-reports')->expectsOutputToContain('1 report(s)')->assertExitCode(0);
    expect($report->refresh()->discord_notified_at)->not->toBeNull();
    expect($already->refresh()->discord_notified_at)->toBe(123);

    $stamp = $report->discord_notified_at;
    $this->artisan('voice:notify-reports')->expectsOutputToContain('0 report(s)')->assertExitCode(0);
    expect($report->refresh()->discord_notified_at)->toBe($stamp);
});

it('prunes audio for old handled reports only', function () {
    $dir = config('athena.voice.report_dir');
    $old = VoiceReport::factory()->create([
        'handled' => true,
        'handled_at' => now()->subDays(40)->getTimestampMs(),
        'target_audio_path' => 'x',
        'reporter_audio_path' => 'y',
    ]);
    $recent = VoiceReport::factory()->create([
        'handled' => true,
        'handled_at' => now()->subDays(5)->getTimestampMs(),
        'target_audio_path' => 'x',
    ]);
    $unhandled = VoiceReport::factory()->create([
        'handled' => false,
        'created_at' => now()->subDays(90)->getTimestampMs(),
        'target_audio_path' => 'x',
    ]);
    foreach ([$old->id, $recent->id, $unhandled->id] as $id) {
        mkdir("$dir/$id", 0777, true);
        file_put_contents("$dir/$id/target.opus", 'a');
        file_put_contents("$dir/$id/reporter.opus", 'a');
    }

    $this->artisan('voice:prune-audio')->assertExitCode(0);

    expect(is_dir("$dir/{$old->id}"))->toBeFalse();
    expect($old->refresh()->target_audio_path)->toBeNull();
    expect($old->reporter_audio_path)->toBeNull();

    expect(is_file("$dir/{$recent->id}/target.opus"))->toBeTrue();
    expect($recent->refresh()->target_audio_path)->toBe('x');

    expect(is_file("$dir/{$unhandled->id}/target.opus"))->toBeTrue();
    expect($unhandled->refresh()->target_audio_path)->toBe('x');
});

it('nulls paths even when the files are already gone', function () {
    $old = VoiceReport::factory()->create([
        'handled' => true,
        'handled_at' => now()->subDays(40)->getTimestampMs(),
        'target_audio_path' => 'x',
    ]);

    $this->artisan('voice:prune-audio')->assertExitCode(0);

    expect($old->refresh()->target_audio_path)->toBeNull();
});

it('schedules both commands', function () {
    $commands = collect(app(\Illuminate\Console\Scheduling\Schedule::class)->events())
        ->map(fn ($event) => $event->command)
        ->filter(fn (?string $command) => $command && str_contains($command, 'voice:'))
        ->map(fn (string $command) => str($command)->after("'artisan' ")->toString())
        ->values()
        ->all();

    expect($commands)->toBe(['voice:notify-reports', 'voice:prune-audio']);
});
