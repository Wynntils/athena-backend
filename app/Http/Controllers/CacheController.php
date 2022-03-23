<?php

namespace App\Http\Controllers;

use App\Http\Libraries\CacheManager;

class CacheController extends Controller
{
    public function getCache($cacheName)
    {
        return CacheManager::getCache($cacheName) ?? response()->json(['message' => "There's not a cache with the provided name."], 404);
    }

    public function getHashes()
    {
        return CacheManager::getHashes();
    }
}
