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

    public static function getCache($cacheName) {
        if (!$cache = self::getCacheObj($cacheName)) {
            return null;
        }

        return Cache::remember($cacheName, $cache->refreshRate(), static function () use ($cacheName, $cache) {
            $data = $cache->generate();
            Cache::forever($cacheName.'.hash', sha1(serialize($data)));
            return $data;
        });
    }

    public static function getCacheObj($cache): ?CacheContract
    {
        if (array_key_exists($cache, self::$cacheTable)) {
            return new self::$cacheTable[$cache];
        }

        return null;
    }

    public static function getHashes(): array
    {
        $hashes = [];
        foreach (self::$cacheTable as $name => $class) {
            $hashes[$name] = Cache::get($name.'.hash');
        }

        return $hashes;
    }
}
