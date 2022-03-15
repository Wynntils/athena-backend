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

    public static function getCache($cache): ?CacheContract
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
            if (!Cache::has($name.'.hash')) {
                $cache = new $class;
                $data = $cache->generate();
                Cache::forever($name.'.hash', md5(serialize($data)));
            }
            $hashes[$name] = Cache::get($name.'.hash');
        }

        return $hashes;
    }
}
