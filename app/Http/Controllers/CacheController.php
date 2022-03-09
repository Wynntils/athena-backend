<?php

namespace App\Http\Controllers;

use App\Http\Libraries\CacheManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class CacheController extends Controller
{
    public function getCache(Request $request, $cache)
    {
        $key = "request|".$request->url();

        if (!$cache = CacheManager::getCache($cache)) {
            return response()->json(['message' => "There's not a cache with the provided name."], 404);
        }

        return Cache::remember($key, $cache->refreshRate(), static function () use ($cache) {
            return $cache->generate();
        });
    }
}
