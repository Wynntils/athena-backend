<?php

namespace App\Console\Commands;

use App\Http\Libraries\Notifications;
use App\Models\User;
use App\Models\VoiceReport;
use DiscordWebhook\EmbedColor;
use Illuminate\Console\Command;

class VoiceNotifyReports extends Command
{
    protected $signature = 'voice:notify-reports';

    protected $description = 'Post a Discord webhook for voice reports that have not been announced yet';

    public function handle(): int
    {
        $reports = VoiceReport::query()->whereNull('discord_notified_at')->orderBy('id')->get();

        $userIds = $reports->flatMap(fn (VoiceReport $r) => [$r->reporter_id, $r->target_id])->unique();
        $usernames = User::query()->whereIn('id', $userIds)->pluck('username', 'id');

        foreach ($reports as $report) {
            Notifications::voice(
                title: "New voice report #{$report->id}",
                description: sprintf(
                    "**[%s → %s](%s)** on %s\n```%s```",
                    $usernames[$report->reporter_id] ?? $report->reporter_id,
                    $usernames[$report->target_id] ?? $report->target_id,
                    route('voice.show', $report),
                    $report->world,
                    str($report->reason)->limit(500)->toString()
                ),
                color: EmbedColor::ORANGE
            );

            $report->discord_notified_at = now()->getTimestampMs();
            $report->save();
        }

        $this->info("Notified {$reports->count()} report(s).");

        return self::SUCCESS;
    }
}
