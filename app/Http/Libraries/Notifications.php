<?php

namespace App\Http\Libraries;

use DiscordWebhook\Embed;
use DiscordWebhook\EmbedColor;
use DiscordWebhook\Webhook;


class Notifications
{

    /**
     * @throws \Symfony\Component\Serializer\Exception\ExceptionInterface
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public static function cape(
        $title = null,
        $description = null,
        EmbedColor $color = null,
        $imageUrl = null,
        $footer = null
    ): void {
        self::sendNotification(config('athena.webhook.discord.webhook.capes'), $title, $description, $color, $imageUrl,
            $footer);
    }

    private static function sendNotification(
        $url,
        $title = null,
        $description = null,
        EmbedColor $color = null,
        $imageUrl = null,
        $footer = null
    ) {
        $wh = new Webhook($url);
        $wh->setUsername(config('athena.webhook.discord.username'))->setAvatar(config('athena.webhook.discord.avatar'));

        $embed = (new Embed())
            ->setTitle($title)
            ->setDescription($description);

        if ($color !== null) {
            $embed->setColor($color);
        }

        if ($imageUrl !== null) {
            $embed->setImage((new Embed\Image())->setUrl($imageUrl));
        }

        if ($footer !== null) {
            $embed->setFooter((new Embed\Footer())->setText($footer));
        }

        $wh->addEmbed($embed)->send();
    }

    public static function log(
        $title = null,
        $description = null,
        EmbedColor $color = null,
        $imageUrl = null,
        $footer = null
    ): void {
        self::sendNotification(config('athena.webhook.discord.webhook.log'), $title, $description, $color, $imageUrl,
            $footer);
    }
}
