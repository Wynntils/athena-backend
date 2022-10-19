<?php

namespace App\Http\Libraries\Requests\Cache;

use App\Http\Enums\ProfessionType;
use Http;
use Illuminate\Http\Client\Pool;

class Leaderboard implements CacheContract
{

    public function refreshRate(): int
    {
        return 3600;
    }

    public function generate(): array
    {
        $result = [];

        $generateProfile = static function ($input, $professionName) use (&$result) {
            $profile = &$result[$input['uuid']];
            $profile['name'] = $input['name'];
            $profile['timePlayed'] = $input['minPlayed'];

            $ranks = &$profile['ranks'];
            $ranks[$professionName] = $input['pos'];
        };

        foreach (self::getLeaderboards() as $leaderboard => $response) {
            $data = $response->json('data');
            if (empty($data)) {
                throw new \Exception('Failed to fetch ' . $leaderboard . ' from Wynn API');
            }
            for ($x = 99; $x >= 91; $x--) {
                $generateProfile($data[$x], $leaderboard);
            }
        }

        return $result;
    }

    private static function getLeaderboards(): array
    {
        return Http::wynn()->pool(static function (Pool $pool) {
            $requests = [];
            foreach (ProfessionType::cases() as $profession) {
                $requests[] = $pool->as($profession->name)->get(config('athena.api.wynn.leaderboards').$profession->leaderboard());
            }
            return $requests;
        });
    }
}
