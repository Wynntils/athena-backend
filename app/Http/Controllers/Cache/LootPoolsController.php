<?php

namespace App\Http\Controllers\Cache;

use App\Http\Controllers\Controller;
use App\Http\Resources\Cache\LootPoolsCacheResource;
use App\Jobs\Cache\RefreshLootPoolsCache;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

#[Group('Cache')]
class LootPoolsController extends Controller
{
    /**
     * Get loot pools
     */
    public function __invoke(): LootPoolsCacheResource|JsonResponse
    {
        $data = Cache::get('cache.lootPools');

        if ($data === null) {
            RefreshLootPoolsCache::dispatchSync();
            $data = Cache::get('cache.lootPools');
        }

        if ($data === null) {
            return response()->json(['error' => 'Cache unavailable'], 503);
        }

        $hash = Cache::get('cache.lootPools.hash');

        return (new LootPoolsCacheResource($data))
            ->response()
            ->header('timestamp', (string) currentTimeMillis())
            ->setCache(['max_age' => 1800, 's_maxage' => 1800, 'public' => true])
            ->setExpires(now()->addSeconds(1800))
            ->setEtag($hash);
    }
}
