<?php

namespace App\Http\Controllers\Cache;

use App\Http\Controllers\Controller;
use App\Http\Resources\Cache\ItemWeightsCacheResource;
use App\Jobs\Cache\RefreshItemWeightsCache;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

#[Group('Cache')]
class ItemWeightsController extends Controller
{
    /**
     * Get item build weights
     */
    public function __invoke(): ItemWeightsCacheResource|JsonResponse
    {
        $data = Cache::get('cache.itemWeights');

        if ($data === null) {
            RefreshItemWeightsCache::dispatchSync();
            $data = Cache::get('cache.itemWeights');
        }

        if ($data === null) {
            return response()->json(['error' => 'Cache unavailable'], 503);
        }

        $hash = Cache::get('cache.itemWeights.hash');

        return (new ItemWeightsCacheResource($data))
            ->response()
            ->header('timestamp', (string) currentTimeMillis())
            ->setCache(['max_age' => 3600, 's_maxage' => 3600, 'public' => true])
            ->setExpires(now()->addSeconds(3600))
            ->setEtag($hash);
    }
}
