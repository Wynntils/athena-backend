<?php

namespace App\Http\Controllers\Cache;

use App\Http\Controllers\Controller;
use App\Http\Resources\Cache\HashesResource;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Support\Facades\Cache;

#[Group('Cache')]
class HashesController extends Controller
{
    /**
     * Get hashes for all active caches
     */
    public function __invoke(): HashesResource
    {
        return new HashesResource([
            'guildList' => Cache::get('cache.guildList.hash'),
            'serverList' => Cache::get('cache.serverList.hash'),
            'itemWeights' => Cache::get('cache.itemWeights.hash'),
            'leaderboard' => Cache::get('cache.leaderboard.hash'),
            'territoryList' => Cache::get('cache.territoryList.hash'),
        ]);
    }
}
