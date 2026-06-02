<?php

namespace App\Http\Controllers\Cache;

use App\Http\Controllers\Controller;
use App\Http\Resources\Cache\LeaderboardCacheResource;
use App\Jobs\Cache\RefreshLeaderboardCache;
use Dedoc\Scramble\Attributes\Group;
use Dedoc\Scramble\Attributes\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

#[Group('Cache')]
class LeaderboardController extends Controller
{
    /**
     * Get the leaderboard
     *
     * Returns top-10 leaderboards keyed by category. Each category maps rank
     * (1-10) to a guild name (guild categories) or player UUID (player categories).
     */
    #[Response(
        examples: [
            [
                'guildLevel' => [
                    '1' => 'Sequoia',
                    '2' => 'Aequitas',
                    '3' => 'The Aquarium',
                    '4' => 'Avicia',
                    '5' => 'Paladins United',
                    '6' => 'Titans Valor',
                    '7' => 'The Broken Gasmask',
                    '8' => 'Anime Lovers',
                    '9' => 'Nerfuria',
                    '10' => 'Empire of Sindria',
                ],
                'combatSoloLevel' => [
                    '1' => '1c4246b0-2734-48d3-a9b9-7ca38e31e2a0',
                    '2' => '72250a1d-144c-48d8-8223-1209ffcaf82d',
                    '3' => 'b7454a9d-ea64-4dea-a4ef-c544d6861b7c',
                    '4' => '1c8078de-f158-4e2a-a19e-82e653b04205',
                    '5' => 'c4e131ff-7e4c-4bb3-baa9-149634309299',
                    '6' => '5c715fab-6a8d-4e00-bb19-9f64d136be68',
                    '7' => '3af095ea-0549-4da6-a61e-92e4f176bdd1',
                    '8' => 'f934c5ba-38a5-4ff3-845c-c894bab20c26',
                    '9' => '47313073-790d-43d8-b22b-8024ff248db3',
                    '10' => 'bccbd1e8-9484-4bec-bd5a-49c437e02d96',
                ],
            ],
        ],
    )]
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
