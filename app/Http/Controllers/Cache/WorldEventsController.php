<?php

namespace App\Http\Controllers\Cache;

use App\Http\Controllers\Controller;
use App\Http\Resources\Cache\Items\WorldEventResource;
use App\Jobs\Cache\RefreshWorldEventsCache;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Cache;

#[Group('Cache')]
class WorldEventsController extends Controller
{
    /**
     * Get world events
     *
     * Returns the currently active and upcoming world events, including their
     * spawn locations, requirements, and reward tiers.
     */
    public function __invoke(): AnonymousResourceCollection|JsonResponse
    {
        $data = Cache::get('cache.worldEvents');

        if ($data === null) {
            RefreshWorldEventsCache::dispatchSync();
            $data = Cache::get('cache.worldEvents');
        }

        if ($data === null) {
            return response()->json(['error' => 'Cache unavailable'], 503);
        }

        $hash = Cache::get('cache.worldEvents.hash');

        return WorldEventResource::collection($data)
            ->response()
            ->header('timestamp', (string) currentTimeMillis())
            ->setCache(['max_age' => 120, 's_maxage' => 120, 'public' => true])
            ->setExpires(now()->addSeconds(120))
            ->setEtag($hash);
    }
}
