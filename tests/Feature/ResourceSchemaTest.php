<?php

namespace Tests\Feature;

use App\Enums\AccountType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class ResourceSchemaTest extends TestCase
{
    use RefreshDatabase;
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

    public function test_cosmetic_asset_resource_includes_required_fields(): void
    {
        $uploader = User::factory()->create();
        $asset    = \App\Models\CosmeticAsset::factory()->approved()->create([
            'uploader_id' => $uploader->id,
            'name'        => 'Test Cape',
            'tags'        => ['size:64'],
        ]);
        $asset->load('uploader', 'votes');

        $resource = new \App\Http\Resources\CosmAssetResource($asset);
        $array    = $resource->toArray(request());

        $this->assertArrayHasKey('sha', $array);
        $this->assertArrayHasKey('type', $array);
        $this->assertArrayHasKey('slot', $array);
        $this->assertArrayHasKey('status', $array);
        $this->assertArrayHasKey('name', $array);
        $this->assertArrayHasKey('visibility', $array);
        $this->assertArrayHasKey('tags', $array);
        $this->assertArrayHasKey('width', $array);
        $this->assertArrayHasKey('height', $array);
        $this->assertArrayHasKey('animated', $array);
        $this->assertArrayHasKey('equip_count', $array);
        $this->assertArrayHasKey('uploaded_at', $array);
        $this->assertArrayHasKey('uploader', $array);
        $this->assertArrayHasKey('votes', $array);

        $this->assertEquals($uploader->username, $array['uploader']['username']);
        $this->assertArrayHasKey('up', $array['votes']);
        $this->assertArrayHasKey('down', $array['votes']);

        $this->assertArrayNotHasKey('pending_name', $array);
        $this->assertArrayNotHasKey('pending_visibility', $array);
        $this->assertArrayNotHasKey('pending_tags', $array);
    }
}
