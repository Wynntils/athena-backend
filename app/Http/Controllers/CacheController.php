<?php

namespace App\Http\Controllers;

use App\Http\Libraries\CacheManager;
use App\Http\Resources\Cache\GuildListCacheResource;
use App\Http\Resources\Cache\ItemWeightsCacheResource;
use App\Http\Resources\Cache\LeaderboardCacheResource;
use App\Http\Resources\Cache\ServerListCacheResource;
use App\Http\Resources\Cache\TerritoryListCacheResource;
use Dedoc\Scramble\Attributes\ExcludeRouteFromDocs;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Cache;

#[Group('Cache')]
class CacheController extends Controller
{
    /**
     * Get the guild list
     */
    public function getGuildList(): GuildListCacheResource|JsonResponse
    {
        return $this->serveCache('guildList', 'v1', new GuildListCacheResource(
            CacheManager::generateCache('guildList', 'v1')
        ));
    }

    /**
     * Get the server list
     */
    public function getServerList(): ServerListCacheResource|JsonResponse
    {
        return $this->serveCache('serverList', 'v1', new ServerListCacheResource(
            CacheManager::generateCache('serverList', 'v1')
        ));
    }

    /**
     * Get item build weights
     */
    public function getItemWeights(): ItemWeightsCacheResource|JsonResponse
    {
        return $this->serveCache('itemWeights', 'v1', new ItemWeightsCacheResource(
            CacheManager::generateCache('itemWeights', 'v1')
        ));
    }

    /**
     * Get the leaderboard
     */
    public function getLeaderboard(): LeaderboardCacheResource|JsonResponse
    {
        return $this->serveCache('leaderboard', 'v1', new LeaderboardCacheResource(
            CacheManager::generateCache('leaderboard', 'v1')
        ));
    }

    /**
     * Get the territory list
     */
    public function getTerritoryList(): TerritoryListCacheResource|JsonResponse
    {
        return $this->serveCache('territoryList', 'v2', new TerritoryListCacheResource(
            CacheManager::generateCache('territoryList', 'v2')
        ));
    }

    #[ExcludeRouteFromDocs]
    public function getCache($cacheName): JsonResponse
    {
        $cache = CacheManager::getCacheClass($cacheName, 'v1');
        if (! $cache) {
            return response()->json(['message' => "There's not a cache with the provided name."], 404);
        }

        $data = CacheManager::generateCache($cacheName, 'v1');
        return $this->serveCache($cacheName, 'v1', new ($this->resourceMap()[$cacheName] ?? JsonResource::class)($data));
    }

    #[ExcludeRouteFromDocs]
    public function getCacheV2($cacheName): JsonResponse
    {
        $cache = CacheManager::getCacheClass($cacheName, 'v2');
        if (! $cache) {
            return response()->json(['message' => "There's not a cache with the provided name."], 404);
        }

        $data = CacheManager::generateCache($cacheName, 'v2');
        return $this->serveCache($cacheName, 'v2', new ($this->v2ResourceMap()[$cacheName] ?? JsonResource::class)($data));
    }

    #[ExcludeRouteFromDocs]
    public function getHashes(): JsonResponse
    {
        return response()->json(['result' => CacheManager::getHashes(), 'message' => 'Successfully grabbed cache hashes.'], 200);
    }

    private function serveCache(string $name, string $version, JsonResource $resource): JsonResponse
    {
        $cache = CacheManager::getCacheClass($name, $version);
        $key = $version === 'v2' ? "v2.$name" : $name;

        return $resource->response()
            ->header('timestamp', currentTimeMillis())
            ->setCache([
                'max_age'  => $cache->refreshRate(),
                's_maxage' => $cache->refreshRate(),
                'public'   => true,
            ])
            ->setExpires(now()->addSeconds($cache->refreshRate()))
            ->setEtag(Cache::get($key.'.hash'));
    }

    private function resourceMap(): array
    {
        return [
            'guildList'   => GuildListCacheResource::class,
            'itemWeights' => ItemWeightsCacheResource::class,
            'leaderboard' => LeaderboardCacheResource::class,
            'serverList'  => ServerListCacheResource::class,
        ];
    }

    private function v2ResourceMap(): array
    {
        return [
            'territoryList' => TerritoryListCacheResource::class,
        ];
    }
}
