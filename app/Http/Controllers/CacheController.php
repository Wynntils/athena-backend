<?php

namespace App\Http\Controllers;

use App\Http\Libraries\CacheManager;
use Illuminate\Support\Facades\Cache;

class CacheController extends Controller
{
    public function getCache($cacheName): \Illuminate\Http\JsonResponse
    {
        $cache = CacheManager::getCacheObj($cacheName);
        if (!$cache) {
            return response()->json(['message' => "There's not a cache with the provided name."], 404);
        }

        $data = CacheManager::generateCache($cacheName);

        return response()->json($data, headers: ['timestamp' => currentTimeMillis()])
            ->setCache([
                'max_age' => $cache->refreshRate(),
                's_maxage' => $cache->refreshRate(),
                'public' => true,
            ])
            ->setExpires(now()->addSeconds($cache->refreshRate()))
            ->setEtag(Cache::get($cacheName.'.hash'));
    }

    public function getHashes(): \Illuminate\Http\JsonResponse
    {
        return response()->json(['result' => CacheManager::getHashes(), 'message' => 'Successfully grabbed cache hashes.'], 200);
    }
}
