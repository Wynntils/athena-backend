<?php

namespace App\Http\Controllers;

use DiscordWebhook\Embed;
use DiscordWebhook\EmbedColor;
use DiscordWebhook\Webhook;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Storage;

class PatreonController extends Controller
{

    public function webhook(Request $request)
    {
        $payload = $request->getContent();
        $signature = $request->header('X_PATREON_SIGNATURE');

        if (!$this->verifySignature($payload, $signature)) {
            abort(403, 'Invalid signature');
        }

        $data = collect(json_decode($payload, true));

        return $this->processEvent($data);
    }

    private function verifySignature($payload, $signature)
    {
        $secret = config('services.patreon.webhook_secret');

        return hash_equals(hash_hmac('sha256', $payload, $secret), $signature);
    }

    private function processEvent(Collection $data): bool
    {
        $includedData = collect($data->get('included'));
        $eventData = collect($data->get('data'));

        $attributes = collect($eventData->get('attributes'));
        $pledgeAmount = $attributes->get('will_pay_amount_cents');
        $pledgeStatus = $attributes->get('last_charge_status');
        $pledgeMonths = $attributes->get('pledge_cadence');

        $relationships = collect($eventData->get('relationships'));
        $patronId = $relationships->pull('user.data.id');
        $campaignId = $relationships->pull('campaign.data.id');
        $tierId = $relationships->pull('currently_entitled_tiers.data.0.id');

        $userData = collect($includedData->where('type', 'user')->where('id', $patronId)->first());
        $campaignData = collect($includedData->where('type', 'campaign')->where('id', $campaignId)->first());
        $tierData = collect($includedData->where('type', 'tier')->where('id', $tierId)->first());

        $userAttributes = collect($userData->get('attributes'));
        $campaignAttributes = collect($campaignData->get('attributes'));
        $tierAttributes = collect($tierData->get('attributes'));

        $tier = $tierAttributes->get('title');

        $patronUrl = $userAttributes->get('url');
        $patronImage = $userAttributes->get('image_url');
        $patronFullName = $userAttributes->get('full_name');
        $discord = $userAttributes->get('social_connections.discord.user_id');
        $patronCount = $campaignAttributes->get('patron_count');

        return $this->sendDiscordMessage(
            \Request::header('X_PATREON_EVENT'),
            $discord !== null ? " <@{$discord}>" : null,
            $patronFullName,
            $tier,
            $pledgeMonths,
            $patronUrl,
            $patronImage,
            $pledgeAmount,
            $pledgeStatus,
            $patronCount
        );
    }

    private function sendDiscordMessage(
        $eventType,
        $message,
        $fullName,
        $tier,
        $pledgeMonths,
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

        $wh = new Webhook(config('services.patreon.discord_webhook'));
        $wh->setUsername('Patreon')
            ->setAvatar('https://c5.patreon.com/external/favicon/apple-touch-icon.png')
            ->setMessage($message);

        $embed = new Embed();
        $embed->setColor($color)->setAuthor(
            (new Embed\Author())
                ->setName($fullName)
                ->setUrl($patronUrl)
                ->setIconUrl($patronImage)
        )->addField(
            (new Embed\Field())
                ->setName('Amount')
                ->setValue(number_format(($pledgeAmount / 100), 2, '.', ' '))
                ->setIsInline(true)
        )->addField(
            (new Embed\Field())
                ->setName('Payment Status')
                ->setValue($pledgeStatus)
                ->setIsInline(true)
        )->addField(
            (new Embed\Field())
                ->setName('Months')
                ->setValue($pledgeMonths)
                ->setIsInline(true)
        )->addField(
            (new Embed\Field())
                ->setName('Tier')
                ->setValue($tier)
                ->setIsInline(false)
        )->setFooter((new Embed\Footer())
            ->setText($eventType." | Total Patrons: ".$patronCount)
        );
        return $wh->addEmbed($embed)->send();
    }

    public function test(Request $request)
    {
        $data = collect(json_decode(Storage::get('test.json'), true));
        $this->processEvent($data);
    }

}
