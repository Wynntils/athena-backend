<?php

namespace App\Listeners;

use App\Http\Libraries\Notifications;
use DiscordWebhook\EmbedColor;
use Illuminate\Http\Client\Events\ConnectionFailed;

class ConnectionFailedListener
{
    public function __construct()
    {
    }

    public function handle(ConnectionFailed $event)
    {
        Notifications::log(
            title: 'Outbound Connection Failed',
            description: sprintf(
                "`%s %s` ```%s```",
                $event->request->method(),
                $event->request->url(),
                $event->request->body()
            ),
            color: EmbedColor::RED
        );
    }
}
