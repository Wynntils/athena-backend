<?php

namespace Tests\Unit\Jobs\Cache;

use App\Http\Libraries\Requests\Cache\GuildList;
use App\Http\Libraries\Requests\Cache\ServerList;
use App\Http\Libraries\Requests\Cache\ItemWeights;
use App\Http\Libraries\Requests\Cache\Leaderboard;
use App\Http\Libraries\Requests\Cache\v2\TerritoryList;
use App\Jobs\Cache\RefreshGuildListCache;
use App\Jobs\Cache\RefreshServerListCache;
use App\Jobs\Cache\RefreshItemWeightsCache;
use App\Jobs\Cache\RefreshLeaderboardCache;
use App\Jobs\Cache\RefreshTerritoryListCache;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class RefreshCacheJobsTest extends TestCase
{
    public function test_refresh_guild_list_writes_data_and_hash(): void
    {
        $data = [['_id' => 'Wynntils', 'prefix' => 'WYN', 'color' => '#fff']];
        $this->mock(GuildList::class, fn ($m) => $m->shouldReceive('generate')->once()->andReturn($data));

        Cache::shouldReceive('forever')->once()->with('cache.guildList', $data);
        Cache::shouldReceive('forever')->once()->with('cache.guildList.hash', hash('sha512', serialize($data)));

        (new RefreshGuildListCache)->handle();
    }

    public function test_refresh_guild_list_logs_and_swallows_exception(): void
    {
        $this->mock(GuildList::class, fn ($m) => $m->shouldReceive('generate')->once()->andThrow(new \Exception('API down')));

        Cache::shouldReceive('forever')->never();
        Log::shouldReceive('error')->once();

        (new RefreshGuildListCache)->handle();
    }

    public function test_refresh_server_list_logs_and_swallows_exception(): void
    {
        $this->mock(ServerList::class, fn ($m) => $m->shouldReceive('generate')->once()->andThrow(new \Exception('API down')));

        Cache::shouldReceive('forever')->never();
        Log::shouldReceive('error')->once();

        (new RefreshServerListCache)->handle();
    }

    public function test_refresh_item_weights_logs_and_swallows_exception(): void
    {
        $this->mock(ItemWeights::class, fn ($m) => $m->shouldReceive('generate')->once()->andThrow(new \Exception('API down')));

        Cache::shouldReceive('forever')->never();
        Log::shouldReceive('error')->once();

        (new RefreshItemWeightsCache)->handle();
    }

    public function test_refresh_leaderboard_logs_and_swallows_exception(): void
    {
        $this->mock(Leaderboard::class, fn ($m) => $m->shouldReceive('generate')->once()->andThrow(new \Exception('API down')));

        Cache::shouldReceive('forever')->never();
        Log::shouldReceive('error')->once();

        (new RefreshLeaderboardCache)->handle();
    }

    public function test_refresh_territory_list_logs_and_swallows_exception(): void
    {
        $this->mock(TerritoryList::class, fn ($m) => $m->shouldReceive('generate')->once()->andThrow(new \Exception('API down')));

        Cache::shouldReceive('forever')->never();
        Log::shouldReceive('error')->once();

        (new RefreshTerritoryListCache)->handle();
    }

    public function test_refresh_server_list_writes_data_and_hash(): void
    {
        $data = ['servers' => ['WC1' => ['firstSeen' => 1000, 'players' => ['Player1']]]];
        $this->mock(ServerList::class, fn ($m) => $m->shouldReceive('generate')->once()->andReturn($data));

        Cache::shouldReceive('forever')->once()->with('cache.serverList', $data);
        Cache::shouldReceive('forever')->once()->with('cache.serverList.hash', hash('sha512', serialize($data)));

        (new RefreshServerListCache)->handle();
    }

    public function test_refresh_item_weights_writes_data_and_hash(): void
    {
        $data = ['wynnpool' => [], 'nori' => []];
        $this->mock(ItemWeights::class, fn ($m) => $m->shouldReceive('generate')->once()->andReturn($data));

        Cache::shouldReceive('forever')->once()->with('cache.itemWeights', $data);
        Cache::shouldReceive('forever')->once()->with('cache.itemWeights.hash', hash('sha512', serialize($data)));

        (new RefreshItemWeightsCache)->handle();
    }

    public function test_refresh_leaderboard_writes_data_and_hash(): void
    {
        $data = ['combatSolo' => ['1' => 'some-uuid']];
        $this->mock(Leaderboard::class, fn ($m) => $m->shouldReceive('generate')->once()->andReturn($data));

        Cache::shouldReceive('forever')->once()->with('cache.leaderboard', $data);
        Cache::shouldReceive('forever')->once()->with('cache.leaderboard.hash', hash('sha512', serialize($data)));

        (new RefreshLeaderboardCache)->handle();
    }

    public function test_refresh_territory_list_writes_data_and_hash(): void
    {
        $data = ['Detlas' => ['guild' => ['name' => 'Wynntils', 'prefix' => 'WYN', 'color' => '#fff'], 'acquired' => '2024-01-01T00:00:00Z', 'location' => ['start' => [0, 0], 'end' => [100, 100]]]];
        $this->mock(TerritoryList::class, fn ($m) => $m->shouldReceive('generate')->once()->andReturn($data));

        Cache::shouldReceive('forever')->once()->with('cache.territoryList', $data);
        Cache::shouldReceive('forever')->once()->with('cache.territoryList.hash', hash('sha512', serialize($data)));

        (new RefreshTerritoryListCache)->handle();
    }
}
