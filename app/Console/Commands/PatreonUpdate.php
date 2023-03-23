<?php

namespace App\Console\Commands;

use App\Enums\AccountType;
use App\Enums\DonatorType;
use App\Models\User;
use DiscordWebhook\Embed;
use DiscordWebhook\EmbedColor;
use DiscordWebhook\Webhook;
use Illuminate\Console\Command;

class PatreonUpdate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'patreon:update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update Patreon data';

    const PATREON_CAMPAGIN_ID = '2422432';

    public function __construct()
    {
        parent::__construct();

        $this->api = \App\Models\PatreonAPI::getApi();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Updating Patreon data');

        // Get all tiers
        $tierData = $this->getTiers();
        // Get all members
        $patreonMembers = $this->getMembers();
        $totalDonators = $patreonMembers->count();
        $this->info(sprintf('Found %d active patrons', $totalDonators));
        $patreonMembers = $patreonMembers->where('social_connections.discord.user_id', '!=', null);
        $this->info(sprintf('Found %d active patrons with a discord account', $patreonMembers->count()));

        // Get all current donators
        $currentDonators = User::where('accountType', AccountType::DONATOR->value)->get(['_id', 'username', 'discordInfo']);

        // Loop through all current donators, and check if they are still donators
        $currentDonators->each(function ($item) use ($tierData, $patreonMembers, $currentDonators) {
            if ($item->donatorType === DonatorType::SPECIAL->value) {
                // Skip special donators (they are not patreon donators)
                return;
            }
            if (!isset($item->discordInfo['id'])) {
                $this->error(sprintf('Donator %s has no discord id', $item->username));
                $item->accountType = AccountType::NORMAL;
                $currentDonators->forget($item->id);
            } else {
                $discordId = $item->discordInfo['id'];

                $donator = $patreonMembers->where('social_connections.discord.user_id', $discordId)->first();

                if ($donator) {
                    $this->info(sprintf('Donator %s (%s) is still a donator', $item->username, $discordId));
                    // Update the donator type if needed
                    $donatorTier = $tierData[$donator['tier_id']];
                    $item->donatorType = DonatorType::fromPatreonLevel($donatorTier['title']);
                } else {
                    $this->warn(sprintf('Donator %s (%s) is not a patreon donator', $item->username, $discordId));
                    $item->accountType = AccountType::NORMAL;
                    $currentDonators->forget($item->id);
                }
            }
            $item->save();
        });

        // Loop through all patreon members, and check if they are already marked as donators
        $patreonMembers->each(function ($item) use ($patreonMembers, $currentDonators) {
            $discordId = $item['social_connections']['discord']['user_id'];

            $user = $currentDonators->where('discordInfo.id', $discordId)->first();

            if ($user) {
                $patreonMembers->forget($item['id']);
            }
        });

        $newDonators = $unhandledDonators = [];

        $this->info(sprintf('Found %d new donators', $patreonMembers->count()));

        // find each new donator in the database and update their account type
        $patreonMembers->each(function ($item) use ($tierData, $patreonMembers, &$newDonators, &$unhandledDonators) {
            $discordId = $item['social_connections']['discord']['user_id'];

            $users = User::where('discordInfo.id', $discordId);

            if ($users->count() > 1) {
                $this->error(sprintf('Patreon Donator %s (%s) discord id has multiple Wynntils Users', $item['attributes']['full_name'], $discordId));
                $unhandledDonators[] = [
                    'member' => $item,
                    'reason' => 'Multiple Wynntils Users found',
                ];
                return;
            }

            if ($users->count() === 0) {
                $this->error(sprintf('Patreon Donator %s (%s) discord id has no Wynntils Users', $item['attributes']['full_name'], $discordId));
                $unhandledDonators[] = [
                    'member' => $item,
                    'reason' => 'No Wynntils Users found',
                ];
                return;
            }

            $user = $users->first();

            if (!$user) {
                $this->error(sprintf('Donator %s (%s) is not found in the database', $item['attributes']['full_name'], $discordId));
                $unhandledDonators[] = [
                    'member' => $item,
                    'reason' => 'User not found in database',
                ];
                return;
            }

            $newDonators[] = $user;

            $user->accountType = AccountType::DONATOR;
            $user->donatorType = DonatorType::fromPatreonLevel($tierData[$item['tier_id']]['title']);
            $user->save();
            $this->info(sprintf('Donator %s (%s) is now marked as donator', $user->username, $discordId));
            $patreonMembers->forget($item['id']);
        });

        $this->info('Done updating Patreon data');

        if (count($newDonators) > 0 || count($unhandledDonators) > 0) {
            $this->sendDiscordMessage($newDonators, $unhandledDonators, $totalDonators);
        }

        return Command::SUCCESS;
    }

    private function sendDiscordMessage(array $newDonators, array $unhandledDonators, int $totalDonators): void
    {
        $message = sprintf('Found %d new donators with %d unhandled donators (total donators: %d)', count($newDonators), count($unhandledDonators), $totalDonators);

        $wh = new Webhook(config('services.patreon.discord_webhook'));
        $wh->setUsername(config('athena.webhook.discord.username'))
            ->setAvatar(config('athena.webhook.discord.avatar'));

        $embed = new Embed();
        $embed->setColor(EmbedColor::AQUA)->setDescription($message);

        if (count($newDonators) > 0) {
            $embed->addField(
                (new Embed\Field())
                    ->setName('New Donators')
                    ->setValue(implode("\n", array_map(function ($item) {
                        return sprintf('%s <@%s>', $item['attributes']['full_name'], $item['social_connections']['discord']['user_id']);
                    }, $newDonators)))
            );
        }

        if (count($unhandledDonators) > 0) {
            $embed->addField(
                (new Embed\Field())
                    ->setName('Unhandled Donators')
                    ->setValue(implode("\n", array_map(function ($item) {
                        return sprintf('%s <@%s> (%s)', $item['member']['attributes']['full_name'], $item['member']['social_connections']['discord']['user_id'], $item['reason']);
                    }, $unhandledDonators)))
            );
        }

        $wh->addEmbed($embed)->send();
    }

    public function getTiers(): \Illuminate\Support\Collection|int
    {
        $tiersQuery = [
            'page' => [
                'count' => 10000
            ],
            'include' => implode(',', [
                'tiers',
            ]),
            'fields' => [
                'tier' => implode(',', [
                    'title',
                    'discord_role_ids'
                ])
            ]
        ];

        $data = $this->api->get_data(sprintf("campaigns/%s?%s", self::PATREON_CAMPAGIN_ID, $this->buildQuery($tiersQuery)));

        if (!is_array($data)) {
            $this->error('Invalid response from Patreon');
            return Command::FAILURE;
        }

        $included = collect($data['included']);

        return $included->map(function ($item) {
            return [
                'id' => $item['id'],
                'title' => $item['attributes']['title'],
                'discord_role_ids' => $item['attributes']['discord_role_ids'][0],
            ];
        })->keyBy('id');
    }

    public function getMembers($activeOnly = true): \Illuminate\Support\Collection|int
    {
        // Get all members from Patreon
        $membersQuery = [
            'page' => [
                'count' => 10000
            ],
            'include' => implode(',', [
                'user',
                'currently_entitled_tiers'
            ]),
            'fields' => [
                'user' => implode(',', [
                    'social_connections'
                ]),
                'member' => implode(',', [
                    'full_name',
                    'is_follower',
                    'last_charge_date',
                    'last_charge_status',
                    'lifetime_support_cents',
                    'currently_entitled_amount_cents',
                    'patron_status'
                ]),
            ]
        ];

        $patreonMembers = collect();

        $this->info('Fetching members from Patreon');
        do {
            $data = $this->api->get_data(sprintf("campaigns/%s/members?%s", self::PATREON_CAMPAGIN_ID, $this->buildQuery($membersQuery)));

            if (!is_array($data)) {
                $this->error('Invalid response from Patreon');
                return Command::FAILURE;
            }

            $includedData = collect($data['included']);
            $memberData = collect($data['data']);

            if ($activeOnly) {
                $memberData = $memberData->where('attributes.patron_status', 'active_patron');
            }

            $members = $memberData->map(function ($item) use ($includedData) {
                $item = collect($item);
                $userData = $includedData->where('type', 'user')->where('id', $item->pull('relationships.user.data.id'))->first();
                return [
                    'id' => $item->get('id'),
                    'attributes' => $item->get('attributes'),
                    'tier_id' => $item->pull('relationships.currently_entitled_tiers.data.0.id'),
                    'social_connections' => $userData['attributes']['social_connections'] ?? null,
                ];
            });

            $patreonMembers = $patreonMembers->merge($members);

            if ($data['meta']['pagination']['cursors']['next'] !== null) {
                $membersQuery['page']['cursor'] = $data['meta']['pagination']['cursors']['next'];
                $this->info('Fetching next page... ' . $membersQuery['page']['cursor']);
            }
        } while ($data['meta']['pagination']['cursors']['next'] !== null);

        return $patreonMembers->keyBy('id');
    }

    private function buildQuery($query)
    {
        return str_replace("%2C", ',', http_build_query($query));
    }
}
