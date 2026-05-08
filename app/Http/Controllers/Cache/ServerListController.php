<?php

namespace App\Http\Controllers\Cache;

use App\Http\Controllers\Controller;
use App\Http\Resources\Cache\ServerListCacheResource;
use App\Jobs\Cache\RefreshServerListCache;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

#[Group('Cache')]
class ServerListController extends Controller
{
    /**
     * Get the server list
     */
    public function __invoke(): ServerListCacheResource|JsonResponse
    {
        $data = Cache::get('cache.serverList');

        if ($data === null) {
            RefreshServerListCache::dispatchSync();
            $data = Cache::get('cache.serverList');
        }

        if ($data === null) {
            return response()->json(['error' => 'Cache unavailable'], 503);
        }

        $hash = Cache::get('cache.serverList.hash');

        return (new ServerListCacheResource($data))
            ->response()
            ->header('timestamp', (string) currentTimeMillis())
            ->setCache(['max_age' => 30, 's_maxage' => 30, 'public' => true])
            ->setExpires(now()->addSeconds(30))
            ->setEtag($hash);
    }
}
