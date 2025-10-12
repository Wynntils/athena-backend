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
        'guildList' => \App\Http\Libraries\Requests\Cache\GuildList::class,
        'guildListWithColors' => \App\Http\Libraries\Requests\Cache\GuildListWithColors::class,
        'itemWeights' => \App\Http\Libraries\Requests\Cache\ItemWeights::class,
    ];

    private static array $v2CacheTable = [
        'territoryList' => \App\Http\Libraries\Requests\Cache\v2\TerritoryList::class,
    ];

    public static function generateCache(string $cacheName, string $version = 'v1')
    {
        if (!$cache = self::getCacheClass($cacheName, $version)) {
            return null;
        }

        $key = self::key($version, $cacheName);

        if (app()->environment('local')) {
            return self::generate($key, $cache);
        }

        return Cache::remember($key, $cache->refreshRate(), static fn() => self::generate($key, $cache));
    }

    private static function generate(string $storageKey, CacheContract $cache)
    {
        try {
            $data = $cache->generate();
        } catch (\Exception $e) {
            if (app()->environment('local')) {
                throw $e;
            }
            \Log::error($e->getMessage());
            return array_merge(Cache::get($storageKey.'.backup', []), ['message' => 'Failed to generate cache. Returning old cache.']);
        }

        Cache::forever($storageKey.'.hash', md5(serialize($data)));
        Cache::forever($storageKey.'.backup', $data);
        return $data;
    }

    public static function getCacheClass(string $cache, string $version = 'v1'): ?CacheContract
    {
        $table = self::table($version);
        if (array_key_exists($cache, $table)) {
            $class = $table[$cache];
            return new $class();
        }
        return null;
    }

    public static function getHashes(string $version = 'v1'): object
    {
        $hashes = [];
        foreach (self::table($version) as $name => $_class) {
            $hashes[$name] = Cache::get(self::key($version, $name).'.hash');
        }
        return cleanNull($hashes);
    }

    private static function table(string $version): array
    {
        if ($version === 'v2') {
            return array_replace(self::$cacheTable, self::$v2CacheTable);
        }

        return self::$cacheTable;
    }

    private static function key(string $version, string $name): string
    {
        return $version === 'v2' ? "v2.$name" : $name;
    }
}
