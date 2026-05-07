<?php

namespace Tests\Feature;

use App\Enums\AccountType;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class ResourceSchemaTest extends TestCase
{
    public function test_get_info_returns_user_resource_shape(): void
    {
        $user = new User([
            'account_type' => AccountType::NORMAL,
            'cosmetic_info' => [],
        ]);
        $user->id = '00000000-0000-0000-0000-000000000001';

        Cache::shouldReceive('remember')
            ->with('user-00000000-0000-0000-0000-000000000001', \Mockery::any(), \Mockery::any())
            ->once()
            ->andReturn($user);

        Cache::shouldReceive('remember')
            ->with(\Mockery::on(fn ($key) => str_starts_with($key, 'cape-texture-')), \Mockery::any(), \Mockery::any())
            ->zeroOrMoreTimes()
            ->andReturn(null);

        $response = $this->withHeaders([
            'User-Agent' => 'Wynntils Artemis Test',
        ])->postJson('/user/getInfo', ['uuid' => $user->id]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'user' => [
                    'accountType',
                    'cosmetics' => ['hasCape', 'hasElytra', 'hasEars', 'texture'],
                ],
            ]);

        $response->assertJsonPath('user.accountType', AccountType::NORMAL->value);
    }
}
