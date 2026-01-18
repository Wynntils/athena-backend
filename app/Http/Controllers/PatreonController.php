<?php

namespace App\Http\Controllers;

use App\Enums\AccountType;
use App\Models\PatreonAPI;
use App\Models\User;
use DiscordWebhook\Embed;
use DiscordWebhook\EmbedColor;
use DiscordWebhook\Webhook;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class PatreonController extends Controller
{
    public function webhook(Request $request)
    {
        $payload = $request->getContent();
        $signature = $request->header('X-Patreon-Signature');

        if (! $this->verifySignature($payload, $signature)) {
            abort(403, 'Invalid signature');
        }

        $data = collect(json_decode($payload, true));

        return $this->processEvent($request->header('X-Patreon-Event'), $data);
    }

    private function verifySignature($payload, $signature)
    {
        $secret = config('services.patreon.webhook_secret');

        return hash_equals(hash_hmac('md5', $payload, $secret), $signature);
    }

    private function processEvent($event, Collection $data): bool
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
        $discord = $userAttributes->pull('social_connections.discord.user_id');
        $patronCount = $campaignAttributes->get('patron_count');

        return $this->sendDiscordMessage(
            $event,
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

        $embed = new Embed;
        $embed->setColor($color)->setAuthor(
            (new Embed\Author)
                ->setName($fullName)
                ->setUrl($patronUrl)
                ->setIconUrl($patronImage)
        )->addField(
            (new Embed\Field)
                ->setName('Amount')
                ->setValue(number_format(($pledgeAmount / 100), 2, '.', ' '))
                ->setIsInline(true)
        )->addField(
            (new Embed\Field)
                ->setName('Payment Status')
                ->setValue($pledgeStatus)
                ->setIsInline(true)
        )->addField(
            (new Embed\Field)
                ->setName('Months')
                ->setValue($pledgeMonths)
                ->setIsInline(true)
        )->setFooter((new Embed\Footer)
            ->setText($eventType.' | Total Patrons: '.$patronCount)
        );
        if ($tier !== null) {
            $embed->addField(
                (new Embed\Field)
                    ->setName('Tier')
                    ->setValue($tier)
                    ->setIsInline(true)
            );
        }

        return $wh->addEmbed($embed)->send();
    }

    public function list(Request $request)
    {
        $api_client = PatreonAPI::getApi();
        $campaign_id = '2422432';

        $currentDonators = User::where('account_type', AccountType::DONATOR->value)->get();

        $queryData = [
            'page' => [
                'count' => 100,
            ],
            'include' => implode(',', [
                'user',
                'currently_entitled_tiers',
            ]),
            'fields' => [
                'user' => implode(',', [
                    'social_connections',
                ]),
                'member' => implode(',', [
                    'full_name',
                    'is_follower',
                    'last_charge_date',
                    'last_charge_status',
                    'lifetime_support_cents',
                    'currently_entitled_amount_cents',
                    'patron_status',
                ]),
                'tier' => implode(',', [
                    'title',
                ]),
            ],
        ];

        $memberList = [];

        $continue = true;

        do {
            $query = str_replace('%2C', ',', http_build_query($queryData));

            $data = $api_client->get_data("campaigns/{$campaign_id}/members?{$query}");

            if (! is_array($data)) {
                return response()->json([
                    'error' => 'Invalid response from Patreon',
                    'data' => $data,
                ], 500);
            }

            $members = collect($data['data'])->map(function ($item) use ($data) {
                $item = collect($item);
                $userData = collect($data['included'])->where('type', 'user')->where('id', $item->pull('relationships.user.data.id'))->first();
                $tierData = collect($data['included'])->where('type', 'tier')->where('id', $item->pull('relationships.currently_entitled_tiers.data.0.id'))->first();

                return $item->mergeRecursive($userData)->put('tier', $tierData['attributes']['title'] ?? 'None');
            })->keyBy('id.1');

            $memberList += $members->toArray();

            if (isset($data['meta']['pagination']['cursors'])) {
                $queryData['page']['cursor'] = $data['meta']['pagination']['cursors']['next'];
            } else {
                $continue = false;
            }
        } while ($continue);

        $memberList = collect($memberList);

        $activePatrons = $memberList->where('attributes.patron_status', 'active_patron');

        $discordList = $activePatrons->map(function ($item) use (&$currentDonators) {
            $item = collect($item);
            $discordId = $item->pull('attributes.social_connections.discord.user_id');
            if (! empty($discordId)) {
                $user = User::whereRaw("discord_info->>'id' = ?", [$discordId])->get();
            } else {
                $user = collect([]);
            }

            $user->each(function ($user) use (&$currentDonators) {
                $currentDonators = $currentDonators->reject(function ($currentDonator) use ($user) {
                    return $currentDonator->id === $user->id;
                });
            });

            foreach ($user as $usr) {
                if ($usr->accountType === AccountType::DONATOR) {
                    $user = collect([$usr]);
                    break;
                }
            }

            return sprintf('%-21s|%-25s|%-10s|%-25s|%-25s',
                '<@'.$discordId.'>',
                '`'.$item->pull('attributes.full_name').'`',
                $item->pull('tier'),
                $user->map(function ($item) {
                    return $item->username;
                })->join(','),
                $user->map(function ($item) {
                    return $item->accountType->value;
                })->join(','),
            );
        });

        $output = "**Current Patreon Donators:**\n".$discordList->join("\n");
        $output .= "\n";
        $output .= "\n";
        $output .= "**Donators not tracked via Patreon:**\n";
        $output .= $currentDonators->map(function ($item) {
            $discordInfo = $item->discord_info ?? [];

            return sprintf('%-21s|%-25s|%-10s',
                '<@'.($discordInfo['id'] ?? 'unknown').'>',
                '`'.$item->username.'`',
                $item->account_type->value,
            );
        })->join("\n");

        return response($output)->header('Content-Type', 'text/plain');
    }
}
