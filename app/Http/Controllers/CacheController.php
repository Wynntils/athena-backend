<?php

namespace App\Http\Controllers;

use App\Http\Libraries\CacheManager;
use Illuminate\Support\Facades\Cache;

class CacheController extends Controller
{
    public function getCache($cacheName): \Illuminate\Http\JsonResponse
    {
        $cache = CacheManager::getCacheClass($cacheName, 'v1');
        if (!$cache) {
            return response()->json(['message' => "There's not a cache with the provided name."], 404);
        }

        $data = CacheManager::generateCache($cacheName, 'v1');

        return response()->json($data, headers: ['timestamp' => currentTimeMillis()])
            ->setCache([
                'max_age' => $cache->refreshRate(),
                's_maxage' => $cache->refreshRate(),
                'public' => true,
            ])
            ->setExpires(now()->addSeconds($cache->refreshRate()))
            ->setEtag(Cache::get($cacheName.'.hash'));
    }

    public function getCacheV2($cacheName): \Illuminate\Http\JsonResponse
    {
        $cache = CacheManager::getCacheClass($cacheName, 'v2');
        if (!$cache) {
            return response()->json(['message' => "There's not a cache with the provided name."], 404);
        }

        $data = CacheManager::generateCache($cacheName, 'v2');
        $ttl  = $cache->refreshRate();
        $key  = "v2.$cacheName";

        return response()->json($data, headers: ['timestamp' => currentTimeMillis()])
            ->setCache(['max_age' => $ttl, 's_maxage' => $ttl, 'public' => true])
            ->setExpires(now()->addSeconds($ttl))
            ->setEtag(Cache::get($key.'.hash'));
    }


    public function getHashes(): \Illuminate\Http\JsonResponse
    {
        return response()->json(['result' => CacheManager::getHashes(), 'message' => 'Successfully grabbed cache hashes.'], 200);
    }
}
