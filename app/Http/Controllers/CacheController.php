<?php

namespace App\Http\Controllers;

use App\Http\Libraries\CacheManager;
use Illuminate\Support\Facades\Cache;

class CacheController extends Controller
{
    public function getCache($cacheName)
    {
        if (!$cache = CacheManager::getCache($cacheName)) {
            return response()->json(['message' => "There's not a cache with the provided name."], 404);
        }

        return Cache::remember($cacheName, $cache->refreshRate(), static function () use ($cacheName, $cache) {
            $data = $cache->generate();
            Cache::forever($cacheName.'.hash', md5(serialize($data)));
            return $data;
        });
    }

    public function getHashes()
    {
        return CacheManager::getHashes();
    }
}
