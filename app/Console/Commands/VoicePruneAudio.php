<?php

namespace App\Console\Commands;

use App\Models\VoiceReport;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\File;

class VoicePruneAudio extends Command
{
    protected $signature = 'voice:prune-audio';

    protected $description = 'Delete voice report audio once the report has been handled for longer than the retention period';

    public function handle(): int
    {
        $cutoff = now()->subDays((int) config('athena.voice.audio_retention_days'))->getTimestampMs();
        $dir = rtrim(config('athena.voice.report_dir'), '/');

        $reports = VoiceReport::query()
            ->where('handled', true)
            ->where('handled_at', '<', $cutoff)
            ->where(function (Builder $q) {
                $q->whereNotNull('reporter_audio_path')->orWhereNotNull('target_audio_path');
            })
            ->get();

        foreach ($reports as $report) {
            File::deleteDirectory("$dir/{$report->id}");
            $report->reporter_audio_path = null;
            $report->target_audio_path = null;
            $report->save();
        }

        $this->info("Pruned audio for {$reports->count()} report(s).");

        return self::SUCCESS;
    }
}
