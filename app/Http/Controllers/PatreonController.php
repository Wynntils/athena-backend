<?php

namespace App\Http\Controllers;

use DiscordWebhook\Embed;
use DiscordWebhook\EmbedColor;
use DiscordWebhook\Webhook;
use Illuminate\Http\Request;

class PatreonController extends Controller
{

    public function webhook(Request $request)
    {
        $payload = $request->getContent();
        $signature = $request->header('X_PATREON_SIGNATURE');

        if (!$this->verifySignature($payload, $signature)) {
            abort(403, 'Invalid signature');
        }

        $eventData = json_decode($payload, true);

        $pledgeAmount = $eventData['data']['attributes']['will_pay_amount_cents'];
        $pledgeStatus = $eventData['data']['attributes']['last_charge_status'];
        $patronId = $eventData['data']['relationships']['user']['data']['id'];
        $campaignId = $eventData['data']['relationships']['campaign']['data']['id'];
        foreach ($eventData['included'] as $included_data) {
            if ($included_data['type'] === 'user' && $included_data['id'] === $patronId) {
                $userData = $included_data;
            }
            if ($included_data['type'] === 'campaign' && $included_data['id'] === $campaignId) {
                $campaignData = $included_data;
            }
        }

        $patronUrl = $userData['attributes']['url'];
        $patronImage = $userData['attributes']['image_url'];
        $patronFullName = $userData['attributes']['full_name'];
        $patronCount = $campaignData['attributes']['patron_count'];
        $discord = $userData['attributes']['social_connections']['discord'];

        if ($discord !== null) {
            $discord = $discord['user_id'];
        }

        $this->sendDiscordMessage(
            $request->header('X_PATREON_EVENT'),
            $discord !== null ? " <@{$discord}>" : null,
            $patronFullName,
            $patronUrl,
            $patronImage,
            $pledgeAmount,
            $pledgeStatus,
            $patronCount
        );

    }

    private function verifySignature($payload, $signature)
    {
        $secret = config('services.patreon.webhook_secret');

        return hash_equals(hash_hmac('sha256', $payload, $secret), $signature);
    }

    private function sendDiscordMessage(
        $eventType,
        $message,
        $fullName,
        $patronUrl,
        $patronImage,
        $pledgeAmount,
        $pledgeStatus,
        $patronCount
    ) {
        $color = match ($eventType) {
            'members:create', 'members:pledge:create' => EmbedColor::GREEN,
            'members:delete', 'members:pledge:delete' => EmbedColor::RED,
            'members:update', 'members:pledge:update' => EmbedColor::GOLD,
            default => EmbedColor::RED
        };

        $webhook = new Webhook(config('services.patreon.discord_webhook'));
        $embed = new Embed();
        $webhook->setMessage($message);
        $embed->setColor($color)->setAuthor(
            (new Embed\Author())
                ->setName($fullName)
                ->setUrl($patronUrl)
                ->setIconUrl($patronImage)
        )->addField(
            (new Embed\Field())
                ->setName('Amount')
                ->setValue($pledgeAmount)
                ->setIsInline(true)
        )->addField(
            (new Embed\Field())
                ->setName('Payment Status')
                ->setValue($pledgeStatus)
                ->setIsInline(true)
        )->setFooter((new Embed\Footer())
            ->setText($eventType." | Total Patrons: ".$patronCount)
        );
        $webhook->addEmbed($embed)->send();
    }

}
