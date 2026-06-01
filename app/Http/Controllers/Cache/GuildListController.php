<?php

namespace App\Http\Controllers\Cache;

use App\Http\Controllers\Controller;
use App\Http\Resources\Cache\Items\GuildSummaryResource;
use App\Jobs\Cache\RefreshGuildListCache;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Cache;

#[Group('Cache')]
class GuildListController extends Controller
{
    /**
     * Get the guild list
     *
     * Returns every Wynncraft guild we know about along with its prefix and color.
     */
    public function __invoke(): AnonymousResourceCollection|JsonResponse
    {
        $data = Cache::get('cache.guildList');

        if ($data === null) {
            RefreshGuildListCache::dispatchSync();
            $data = Cache::get('cache.guildList');
        }

        if ($data === null) {
            return response()->json(['error' => 'Cache unavailable'], 503);
        }

        $hash = Cache::get('cache.guildList.hash');

        return GuildSummaryResource::collection($data)
            ->response()
            ->header('timestamp', (string) currentTimeMillis())
            ->setCache(['max_age' => 3600, 's_maxage' => 3600, 'public' => true])
            ->setExpires(now()->addSeconds(3600))
            ->setEtag($hash);
    }
}
