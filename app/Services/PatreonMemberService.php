<?php

namespace App\Services;

use App\Enums\AccountType;
use App\Enums\DonatorType;
use App\Models\User;

class PatreonMemberService
{
    private const REVOKE_STATUSES = ['Declined', 'Fraud', 'Refunded', 'Deleted'];

    /**
     * @return array{outcome: string, user: ?User, reason: ?string}
     */
    public function syncMember(
        string $eventType,
        ?string $discordId,
        ?string $tierTitle,
        ?string $pledgeStatus
    ): array {
        if (in_array($eventType, ['members:create', 'members:pledge:create'], true)) {
            return $this->grant($discordId, $tierTitle);
        }

        if (in_array($eventType, ['members:delete', 'members:pledge:delete'], true)) {
            return $this->revoke($discordId);
        }

        if (in_array($eventType, ['members:update', 'members:pledge:update'], true)) {
            if ($pledgeStatus === 'Paid') {
                return $this->grant($discordId, $tierTitle);
            }

            if (in_array($pledgeStatus, self::REVOKE_STATUSES, true)) {
                return $this->revoke($discordId);
            }

            return ['outcome' => 'skipped', 'user' => null, 'reason' => 'Unrecognised pledge status'];
        }

        return ['outcome' => 'skipped', 'user' => null, 'reason' => 'Unrecognised event type'];
    }

    /**
     * @return array{outcome: string, user: ?User, reason: ?string}
     */
    private function grant(?string $discordId, ?string $tierTitle): array
    {
        if ($discordId === null) {
            return ['outcome' => 'unhandled', 'user' => null, 'reason' => 'No Discord account linked'];
        }

        $user = User::byDiscordId($discordId)->first();

        if ($user === null) {
            return ['outcome' => 'unhandled', 'user' => null, 'reason' => 'No Wynntils account found'];
        }

        if ($user->donator_type === DonatorType::SPECIAL) {
            return ['outcome' => 'skipped', 'user' => $user, 'reason' => 'Special donator, not modified'];
        }

        $wasAlreadyDonator = $user->account_type === AccountType::DONATOR;

        if ($tierTitle !== null) {
            $donatorType = DonatorType::tryFrom('Patreon '.$tierTitle);

            if ($donatorType === null) {
                return ['outcome' => 'unhandled', 'user' => $user, 'reason' => 'Unrecognised tier: '.$tierTitle];
            }

            $user->donator_type = $donatorType;
        }

        $user->account_type = AccountType::DONATOR;
        $user->save();

        return [
            'outcome' => $wasAlreadyDonator ? 'tier_updated' : 'granted',
            'user' => $user,
            'reason' => null,
        ];
    }

    /**
     * @return array{outcome: string, user: ?User, reason: ?string}
     */
    private function revoke(?string $discordId): array
    {
        if ($discordId === null) {
            return ['outcome' => 'unhandled', 'user' => null, 'reason' => 'No Discord account linked'];
        }

        $user = User::byDiscordId($discordId)->first();

        if ($user === null) {
            return ['outcome' => 'unhandled', 'user' => null, 'reason' => 'No Wynntils account found'];
        }

        if ($user->donator_type === DonatorType::SPECIAL) {
            return ['outcome' => 'skipped', 'user' => $user, 'reason' => 'Special donator, not modified'];
        }

        $user->account_type = AccountType::NORMAL;
        $user->donator_type = DonatorType::NONE;
        $user->save();

        return ['outcome' => 'revoked', 'user' => $user, 'reason' => null];
    }
}
