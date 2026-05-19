<?php

use App\Enums\AccountType;
use App\Models\User;
use App\Services\PatreonMemberService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(Tests\TestCase::class, RefreshDatabase::class);

function patreonPayload(string $discordId, string $tierId = 'tier-1', string $tierTitle = 'Level 1'): array
{
    return [
        'data' => [
            'attributes' => [
                'will_pay_amount_cents' => 500,
                'last_charge_status' => null,
                'pledge_cadence' => 1,
            ],
            'relationships' => [
                'user' => ['data' => ['id' => 'user-1', 'type' => 'user']],
                'campaign' => ['data' => ['id' => 'campaign-1', 'type' => 'campaign']],
                'currently_entitled_tiers' => ['data' => [['id' => $tierId, 'type' => 'tier']]],
            ],
        ],
        'included' => [
            [
                'type' => 'user',
                'id' => 'user-1',
                'attributes' => [
                    'url' => 'https://patreon.com/user',
                    'image_url' => 'https://example.com/img.png',
                    'full_name' => 'Test User',
                    'social_connections' => [
                        'discord' => ['user_id' => $discordId],
                    ],
                ],
            ],
            [
                'type' => 'campaign',
                'id' => 'campaign-1',
                'attributes' => ['patron_count' => 42],
            ],
            [
                'type' => 'tier',
                'id' => $tierId,
                'attributes' => ['title' => $tierTitle],
            ],
        ],
    ];
}

function postWebhook(mixed $test, string $event, array $payload, string $secret = 'test-secret'): \Illuminate\Testing\TestResponse
{
    $body = json_encode($payload);
    $signature = hash_hmac('md5', $body, $secret);

    return $test->call('POST', '/patreon/webhook', [], [], [], [
        'HTTP_X_PATREON_SIGNATURE' => $signature,
        'HTTP_X_PATREON_EVENT' => $event,
        'CONTENT_TYPE' => 'application/json',
    ], $body);
}

beforeEach(function () {
    config(['services.patreon.webhook_secret' => 'test-secret']);
    config(['services.patreon.discord_webhook' => 'https://discord.test/webhook']);
});

it('rejects requests with an invalid signature', function () {
    $body = json_encode(patreonPayload('111'));

    $response = $this->call('POST', '/patreon/webhook', [], [], [], [
        'HTTP_X_PATREON_SIGNATURE' => 'invalid-signature',
        'HTTP_X_PATREON_EVENT' => 'members:create',
        'CONTENT_TYPE' => 'application/json',
    ], $body);

    $response->assertForbidden();
});

it('grants donator status on a create event with a matching user', function () {
    $user = User::factory()->create([
        'discord_info' => ['id' => '111', 'username' => 'tester'],
    ]);

    $this->mock(PatreonMemberService::class)
        ->shouldReceive('syncMember')
        ->with('members:create', '111', 'Level 1', null)
        ->once()
        ->andReturn(['outcome' => 'granted', 'user' => $user, 'reason' => null]);

    postWebhook($this, 'members:create', patreonPayload('111'))->assertSuccessful();
});

it('service is called with correct arguments on delete event', function () {
    $user = User::factory()->create([
        'account_type' => AccountType::DONATOR,
        'discord_info' => ['id' => '222', 'username' => 'tester'],
    ]);

    $this->mock(PatreonMemberService::class)
        ->shouldReceive('syncMember')
        ->with('members:delete', '222', null, null)
        ->once()
        ->andReturn(['outcome' => 'revoked', 'user' => $user, 'reason' => null]);

    postWebhook($this, 'members:delete', patreonPayload('222', 'tier-1', ''))->assertSuccessful();
});

it('handles unhandled outcome without crashing', function () {
    $this->mock(PatreonMemberService::class)
        ->shouldReceive('syncMember')
        ->once()
        ->andReturn(['outcome' => 'unhandled', 'user' => null, 'reason' => 'No Wynntils account found']);

    postWebhook($this, 'members:create', patreonPayload('999'))->assertSuccessful();
});
