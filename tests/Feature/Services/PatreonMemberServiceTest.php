<?php

use App\Enums\AccountType;
use App\Enums\DonatorType;
use App\Models\User;
use App\Services\PatreonMemberService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(Tests\TestCase::class, RefreshDatabase::class);

function userWithDiscord(
    string $discordId,
    AccountType $accountType = AccountType::NORMAL,
    ?DonatorType $donatorType = null
): User {
    return User::factory()->create([
        'account_type' => $accountType,
        'donator_type' => $donatorType,
        'discord_info' => ['id' => $discordId, 'username' => 'testuser'],
    ]);
}

it('grants donator status on create event', function () {
    $user = userWithDiscord('111');

    $result = app(PatreonMemberService::class)->syncMember('members:create', '111', 'Level 1', null);

    expect($result['outcome'])->toBe('granted');
    expect($result['user']->id)->toBe($user->id);

    $user->refresh();
    expect($user->account_type)->toBe(AccountType::DONATOR);
    expect($user->donator_type)->toBe(DonatorType::PATREON_LEVEL_1);
});

it('sets correct DonatorType from tier title on grant', function () {
    userWithDiscord('111');

    app(PatreonMemberService::class)->syncMember('members:pledge:create', '111', 'Level 2', null);

    expect(User::byDiscordId('111')->first()->donator_type)->toBe(DonatorType::PATREON_LEVEL_2);
});

it('returns unhandled when discord id is null on create', function () {
    $result = app(PatreonMemberService::class)->syncMember('members:create', null, 'Level 1', null);

    expect($result['outcome'])->toBe('unhandled')
        ->and($result['reason'])->toBe('No Discord account linked');
});

it('returns unhandled when no matching wynntils user on create', function () {
    $result = app(PatreonMemberService::class)->syncMember('members:create', 'nonexistent-discord-id', 'Level 1', null);

    expect($result['outcome'])->toBe('unhandled')
        ->and($result['reason'])->toBe('No Wynntils account found');
});

it('skips special donator and leaves account unchanged on create', function () {
    $user = userWithDiscord('111', AccountType::DONATOR, DonatorType::SPECIAL);

    $result = app(PatreonMemberService::class)->syncMember('members:create', '111', 'Level 3', null);

    expect($result['outcome'])->toBe('skipped');

    $user->refresh();
    expect($user->donator_type)->toBe(DonatorType::SPECIAL);
    expect($user->account_type)->toBe(AccountType::DONATOR);
});

it('revokes donator status on delete event', function () {
    $user = userWithDiscord('111', AccountType::DONATOR, DonatorType::PATREON_LEVEL_2);

    $result = app(PatreonMemberService::class)->syncMember('members:delete', '111', null, null);

    expect($result['outcome'])->toBe('revoked');

    $user->refresh();
    expect($user->account_type)->toBe(AccountType::NORMAL);
    expect($user->donator_type)->toBe(DonatorType::NONE);
});

it('skips special donator on delete', function () {
    $user = userWithDiscord('111', AccountType::DONATOR, DonatorType::SPECIAL);

    $result = app(PatreonMemberService::class)->syncMember('members:pledge:delete', '111', null, null);

    expect($result['outcome'])->toBe('skipped');
    $user->refresh();
    expect($user->donator_type)->toBe(DonatorType::SPECIAL);
});

it('grants on update event when pledge status is Paid', function () {
    $user = userWithDiscord('111');

    $result = app(PatreonMemberService::class)->syncMember('members:update', '111', 'Level 2', 'Paid');

    expect($result['outcome'])->toBe('granted');
    $user->refresh();
    expect($user->account_type)->toBe(AccountType::DONATOR);
    expect($user->donator_type)->toBe(DonatorType::PATREON_LEVEL_2);
});

it('revokes on update event when pledge status is Declined', function () {
    $user = userWithDiscord('111', AccountType::DONATOR, DonatorType::PATREON_LEVEL_1);

    $result = app(PatreonMemberService::class)->syncMember('members:update', '111', null, 'Declined');

    expect($result['outcome'])->toBe('revoked');
    $user->refresh();
    expect($user->account_type)->toBe(AccountType::NORMAL);
});

it('revokes on update event when pledge status is Fraud', function () {
    $user = userWithDiscord('111', AccountType::DONATOR, DonatorType::PATREON_LEVEL_1);

    app(PatreonMemberService::class)->syncMember('members:pledge:update', '111', null, 'Fraud');

    $user->refresh();
    expect($user->account_type)->toBe(AccountType::NORMAL);
});

it('skips update event when pledge status is null', function () {
    userWithDiscord('111');

    $result = app(PatreonMemberService::class)->syncMember('members:update', '111', null, null);

    expect($result['outcome'])->toBe('skipped')
        ->and($result['reason'])->toBe('Unrecognised pledge status');
});

it('returns tier_updated when existing donator gets a tier change', function () {
    $user = userWithDiscord('111', AccountType::DONATOR, DonatorType::PATREON_LEVEL_1);

    $result = app(PatreonMemberService::class)->syncMember('members:create', '111', 'Level 2', null);

    expect($result['outcome'])->toBe('tier_updated');
    $user->refresh();
    expect($user->donator_type)->toBe(DonatorType::PATREON_LEVEL_2);
    expect($user->account_type)->toBe(AccountType::DONATOR);
});
