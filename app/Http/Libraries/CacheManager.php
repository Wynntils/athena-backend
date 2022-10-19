<?php

namespace App\Http\Libraries;

use App\Http\Libraries\Requests\Cache\CacheContract;
use Illuminate\Support\Facades\Cache;

class CacheManager
{
    private static array $cacheTable = [
        'gatheringSpots' => \App\Http\Libraries\Requests\Cache\GatheringSpots::class,
        'ingredientList' => \App\Http\Libraries\Requests\Cache\IngredientList::class,
        'itemList' => \App\Http\Libraries\Requests\Cache\ItemList::class,
        'leaderboard' => \App\Http\Libraries\Requests\Cache\Leaderboard::class,
        'mapLocations' => \App\Http\Libraries\Requests\Cache\MapLocations::class,
        'serverList' => \App\Http\Libraries\Requests\Cache\ServerList::class,
        'territoryList' => \App\Http\Libraries\Requests\Cache\TerritoryList::class,
    ];

    public static function generateCache($cacheName) {
        if (!$cache = self::getCacheClass($cacheName)) {
            return null;
        }

        if (app()->environment('local')) {
            return self::generate($cacheName, $cache);
        }

        return Cache::remember($cacheName, $cache->refreshRate(), static function () use ($cacheName, $cache) {
            self::generate($cacheName, $cache);
        });
    }

    private static function generate($cacheName, CacheContract $cache) {
        try {
            $data = $cache->generate();
        } catch (\Exception $e) {
            \Log::error($e->getMessage());
            // If the cache fails to generate, we want to return the old cache
            return array_merge(Cache::get($cacheName.'.backup', []), ['message' => 'Failed to generate cache. Returning old cache.']);
        }
        Cache::forever($cacheName.'.hash', md5(serialize($data)));
        Cache::forever($cacheName.'.backup', $data);
        return $data;
    }

    public static function getCacheClass($cache): ?CacheContract
    {
        if (array_key_exists($cache, self::$cacheTable)) {
            return new self::$cacheTable[$cache];
        }

        return null;
    }

    public static function getHashes(): object
    {
        $hashes = [];
        foreach (self::$cacheTable as $name => $class) {
            $hashes[$name] = Cache::get($name.'.hash');
        }

        return cleanNull($hashes);
    }
}
