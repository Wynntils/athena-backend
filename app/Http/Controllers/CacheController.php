<?php

namespace App\Http\Controllers;

use App\Http\Libraries\CacheManager;
use App\Http\Resources\Cache\GuildListCacheResource;
use App\Http\Resources\Cache\ItemWeightsCacheResource;
use App\Http\Resources\Cache\LeaderboardCacheResource;
use App\Http\Resources\Cache\ServerListCacheResource;
use App\Http\Resources\Cache\TerritoryListCacheResource;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Dedoc\Scramble\Attributes\ExcludeRouteFromDocs;
#[Group('Cache')]
class CacheController extends Controller
{
    private static array $resourceMap = [
        'guildList'   => GuildListCacheResource::class,
        'itemWeights' => ItemWeightsCacheResource::class,
        'leaderboard' => LeaderboardCacheResource::class,
        'serverList'  => ServerListCacheResource::class,
    ];

    private static array $v2ResourceMap = [
        'territoryList' => TerritoryListCacheResource::class,
    ];

    /**
     * Get a named cache dataset
     */
    public function getCache($cacheName): JsonResponse
    {
        $cache = CacheManager::getCacheClass($cacheName, 'v1');
        if (! $cache) {
            return response()->json(['message' => "There's not a cache with the provided name."], 404);
        }

        $data = CacheManager::generateCache($cacheName, 'v1');

        $resourceClass = self::$resourceMap[$cacheName] ?? null;
        $response = $resourceClass
            ? (new $resourceClass($data))->response()
            : response()->json($data);

        return $response
            ->header('timestamp', currentTimeMillis())
            ->setCache([
                'max_age'  => $cache->refreshRate(),
                's_maxage' => $cache->refreshRate(),
                'public'   => true,
            ])
            ->setExpires(now()->addSeconds($cache->refreshRate()))
            ->setEtag(Cache::get($cacheName.'.hash'));
    }

    /**
     * Get a named v2 cache dataset
     */
    public function getCacheV2($cacheName): JsonResponse
    {
        $cache = CacheManager::getCacheClass($cacheName, 'v2');
        if (! $cache) {
            return response()->json(['message' => "There's not a cache with the provided name."], 404);
        }

        $data = CacheManager::generateCache($cacheName, 'v2');
        $ttl = $cache->refreshRate();
        $key = "v2.$cacheName";

        $resourceClass = self::$v2ResourceMap[$cacheName] ?? null;
        $response = $resourceClass
            ? (new $resourceClass($data))->response()
            : response()->json($data);

        return $response
            ->header('timestamp', currentTimeMillis())
            ->setCache(['max_age' => $ttl, 's_maxage' => $ttl, 'public' => true])
            ->setExpires(now()->addSeconds($ttl))
            ->setEtag(Cache::get($key.'.hash'));
    }

    #[ExcludeRouteFromDocs]
    public function getHashes(): JsonResponse
    {
        return response()->json(['result' => CacheManager::getHashes(), 'message' => 'Successfully grabbed cache hashes.'], 200);
    }
}
