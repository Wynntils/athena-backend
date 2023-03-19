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
        self::sendNotification(
            url: config('athena.webhook.discord.webhook.capes'),
            title: $title,
            description: $description,
            color: $color,
            imageUrl: $imageUrl,
            footer: $footer
        );
    }

    public static function crash(
        $content = null,
        $title = null,
        $description = null,
        EmbedColor $color = null,
        $imageUrl = null,
        $footer = null
    ): void {
        self::sendNotification(
            url: config('athena.webhook.discord.webhook.crash'),
            content: $content,
            title: $title,
            description: $description,
            color: $color,
            imageUrl: $imageUrl,
            footer: $footer
        );
    }

    private static function sendNotification(
        $url,
        $content = null,
        $title = null,
        $description = null,
        EmbedColor $color = null,
        $imageUrl = null,
        $footer = null,
    ) {
        $wh = new Webhook($url);
        $wh->setUsername(config('athena.webhook.discord.username'))->setAvatar(config('athena.webhook.discord.avatar'));

        if ($content !== null) {
            $wh->setMessage($content);
        }

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
        $content = null,
        $title = null,
        $description = null,
        EmbedColor $color = null,
        $imageUrl = null,
        $footer = null
    ): void {
        self::sendNotification(
            url: config('athena.webhook.discord.webhook.log'),
            content: $content,
            title: $title,
            description: $description,
            color: $color,
            imageUrl: $imageUrl,
            footer: $footer
        );

    }
}
