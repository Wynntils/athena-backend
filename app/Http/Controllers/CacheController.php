<?php

namespace App\Http\Controllers;

use App\Managers\CacheManager;
use Illuminate\Support\Facades\Cache;

class CacheController extends Controller
{
    public function getCache(string $cacheName): \Illuminate\Http\JsonResponse
    {
        $cache = CacheManager::getCacheClass($cacheName);

        if (!$cache) {
            return response()->json(['message' => "No cache found with the provided name."], 404);
        }

        $data = CacheManager::getCache($cacheName);

        if (empty($data)) {
            return response()->json(['message' => "Cache is empty or not yet generated."], 404);
        }

        return response()->json($data, headers: ['timestamp' => now()->timestamp * 1000])
            ->setCache([
                           'max_age' => $cache->refreshRate(),
                           's_maxage' => $cache->refreshRate(),
                           'public' => true,
                       ])
            ->setExpires(now()->addSeconds($cache->refreshRate()))
            ->setEtag(Cache::get("{$cacheName}.hash"));
    }

    public function getHashes(): \Illuminate\Http\JsonResponse
    {
        return response()->json(
            [
                'result' => CacheManager::getHashes(),
                'message' => 'Successfully retrieved cache hashes.',
            ],
            200
        );
    }
}
