<?php

namespace App\Http\Controllers;

use App\Http\Libraries\CacheManager;

class CacheController extends Controller
{
    public function getCache($cacheName): \Illuminate\Http\JsonResponse
    {
        return response()->json(CacheManager::getCache($cacheName)) ?? response()->json(['message' => "There's not a cache with the provided name."], 404);
    }

    public function getHashes(): \Illuminate\Http\JsonResponse
    {
        return response()->json(['result' => CacheManager::getHashes(), 'message' => 'Successfully grabbed cache hashes.'], 200);
    }
}
