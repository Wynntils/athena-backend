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
            content: '<@&980223126619176960>',
            title: 'Connection Failed',
            description: sprintf(
                "`Routes -> %s -> /%s",
                $event->request->method(),
                $event->request->url()
            ),
            color: EmbedColor::RED
        );
    }
}
