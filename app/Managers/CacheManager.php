<?php

namespace App\Managers;

use App\Http\Libraries\Requests\Cache\CacheContract;
use Illuminate\Support\Facades\Cache;

class CacheManager
{
    public static array $cacheTable = [
        'gatheringSpots' => \App\Http\Libraries\Requests\Cache\GatheringSpots::class,
        'ingredientList' => \App\Http\Libraries\Requests\Cache\IngredientList::class,
//        'itemList' => \App\Http\Libraries\Requests\Cache\ItemList::class,
        'leaderboard' => \App\Http\Libraries\Requests\Cache\Leaderboard::class,
        'mapLocations' => \App\Http\Libraries\Requests\Cache\MapLocations::class,
        'serverList' => \App\Http\Libraries\Requests\Cache\ServerList::class,
        'territoryList' => \App\Http\Libraries\Requests\Cache\TerritoryList::class,
        'guildList' => \App\Http\Libraries\Requests\Cache\GuildList::class, // All guilds
        'guildListWithColors' => \App\Http\Libraries\Requests\Cache\GuildListWithColors::class, // Only guilds with color set
    ];

    public static function getCache(string $cacheName): array
    {
        return Cache::get("{$cacheName}.data", []); // Return empty array if cache is missing
    }

    public static function getCacheClass(string $cache): ?CacheContract
    {
        return array_key_exists($cache, self::$cacheTable) ? new self::$cacheTable[$cache] : null;
    }

    public static function getHashes(): array
    {
        $hashes = [];
        foreach (self::$cacheTable as $name => $class) {
            $hashes[$name] = Cache::get("{$name}.hash");
        }
        return array_filter($hashes);
    }
}
