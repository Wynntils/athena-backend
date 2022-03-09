<?php

namespace App\Http\Libraries;

use App\Http\Libraries\Requests\Cache\CacheContract;

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

    public static function getCache($cache): ?CacheContract
    {
        if (array_key_exists($cache, self::$cacheTable)) {
            return new self::$cacheTable[$cache];
        }

        return null;
    }
}
