<?php

namespace App\Http\Controllers\Cache;

use App\Http\Controllers\Controller;
use App\Http\Resources\Cache\LeaderboardCacheResource;
use App\Jobs\Cache\RefreshLeaderboardCache;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

#[Group('Cache')]
class LeaderboardController extends Controller
{
    /**
     * Get the leaderboard
     */
    public function __invoke(): LeaderboardCacheResource|JsonResponse
    {
        $data = Cache::get('cache.leaderboard');

        if ($data === null) {
            RefreshLeaderboardCache::dispatchSync();
            $data = Cache::get('cache.leaderboard');
        }

        if ($data === null) {
            return response()->json(['error' => 'Cache unavailable'], 503);
        }

        $hash = Cache::get('cache.leaderboard.hash');

        return (new LeaderboardCacheResource($data))
            ->response()
            ->header('timestamp', (string) currentTimeMillis())
            ->setCache(['max_age' => 600, 's_maxage' => 600, 'public' => true])
            ->setExpires(now()->addSeconds(600))
            ->setEtag($hash);
    }
}
