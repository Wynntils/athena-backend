<?php

namespace Tests\Feature\Cache;

use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class CacheControllersTest extends TestCase
{
    public function test_guild_list_returns_cached_data_with_headers(): void
    {
        $data = [['_id' => 'Wynntils', 'prefix' => 'WYN', 'color' => '#ffffff']];
        Cache::put('cache.guildList', $data);
        Cache::put('cache.guildList.hash', 'abc123hash');

        $response = $this->getJson('/cache/get/guildList');

        $response->assertStatus(200)->assertJson($data);
        $this->assertNotNull($response->headers->get('timestamp'));
        $this->assertStringContainsString('max-age=3600', $response->headers->get('Cache-Control'));
        $this->assertSame('"abc123hash"', $response->headers->get('ETag'));
    }

    public function test_server_list_returns_cached_data_with_headers(): void
    {
        $data = ['servers' => ['WC1' => ['firstSeen' => 1000000, 'players' => ['Player1']]]];
        Cache::put('cache.serverList', $data);
        Cache::put('cache.serverList.hash', 'serverhash');

        $response = $this->getJson('/cache/get/serverList');

        $response->assertStatus(200)->assertJson($data);
        $this->assertStringContainsString('max-age=30', $response->headers->get('Cache-Control'));
        $this->assertSame('"serverhash"', $response->headers->get('ETag'));
    }

    public function test_item_weights_returns_cached_data_with_headers(): void
    {
        $data = ['wynnpool' => ['Warchief Mask' => ['tank' => ['strengthPoints' => 1.0]]], 'nori' => []];
        Cache::put('cache.itemWeights', $data);
        Cache::put('cache.itemWeights.hash', 'weighthash');

        $response = $this->getJson('/cache/get/itemWeights');

        $response->assertStatus(200)->assertJson($data);
        $this->assertStringContainsString('max-age=3600', $response->headers->get('Cache-Control'));
    }

    public function test_leaderboard_returns_cached_data_with_headers(): void
    {
        $data = ['combatSolo' => ['1' => 'some-uuid', '2' => 'other-uuid']];
        Cache::put('cache.leaderboard', $data);
        Cache::put('cache.leaderboard.hash', 'lbhash');

        $response = $this->getJson('/cache/get/leaderboard');

        $response->assertStatus(200)->assertJson($data);
        $this->assertStringContainsString('max-age=600', $response->headers->get('Cache-Control'));
    }

    public function test_territory_list_returns_cached_data_with_headers(): void
    {
        $data = ['Detlas' => ['guild' => ['name' => 'Wynntils', 'prefix' => 'WYN', 'color' => '#fff'], 'acquired' => '2024-01-01T00:00:00Z', 'location' => ['start' => [0, 0], 'end' => [100, 100]]]];
        Cache::put('cache.territoryList', $data);
        Cache::put('cache.territoryList.hash', 'terrihash');

        $response = $this->getJson('/cache/get/territoryList');

        $response->assertStatus(200)->assertJson($data);
        $this->assertStringContainsString('max-age=15', $response->headers->get('Cache-Control'));
    }

    public function test_hashes_returns_flat_map(): void
    {
        Cache::put('cache.guildList.hash', 'gh');
        Cache::put('cache.serverList.hash', 'sh');
        Cache::put('cache.itemWeights.hash', 'iwh');
        Cache::put('cache.leaderboard.hash', 'lbh');
        Cache::put('cache.territoryList.hash', 'tlh');

        $response = $this->getJson('/cache/getHashes');

        $response->assertStatus(200)->assertExactJson([
            'guildList'     => 'gh',
            'serverList'    => 'sh',
            'itemWeights'   => 'iwh',
            'leaderboard'   => 'lbh',
            'territoryList' => 'tlh',
        ]);
    }
}
