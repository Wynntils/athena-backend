<?php

use App\Http\Libraries\Notifications;
use DiscordWebhook\EmbedColor;

uses(Tests\TestCase::class);

it('is a no-op when the voice webhook is unconfigured', function () {
    config(['athena.webhook.discord.webhook.voice' => null]);
    Notifications::voice(title: 'x', description: 'y', color: EmbedColor::RED);
})->throwsNoExceptions();
