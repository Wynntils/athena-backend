<?php

namespace App\Http\Controllers\Cache;

use App\Http\Controllers\Controller;
use App\Http\Resources\Cache\TerritoryListCacheResource;
use App\Jobs\Cache\RefreshTerritoryListCache;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

#[Group('Cache')]
class TerritoryListController extends Controller
{
    /**
     * Get the territory list
     */
    public function __invoke(): TerritoryListCacheResource|JsonResponse
    {
        $data = Cache::get('cache.territoryList');

        if ($data === null) {
            RefreshTerritoryListCache::dispatchSync();
            $data = Cache::get('cache.territoryList');
        }

        if ($data === null) {
            return response()->json(['error' => 'Cache unavailable'], 503);
        }

        $hash = Cache::get('cache.territoryList.hash');

        return (new TerritoryListCacheResource($data))
            ->response()
            ->header('timestamp', currentTimeMillis())
            ->setCache(['max_age' => 15, 's_maxage' => 15, 'public' => true])
            ->setExpires(now()->addSeconds(15))
            ->setEtag($hash);
    }
}
