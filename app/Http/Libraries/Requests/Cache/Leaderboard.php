<?php

namespace App\Http\Libraries\Requests\Cache;

use App\Http\Enums\ProfessionType;
use App\Http\Libraries\Requests\WynnRequest;

class Leaderboard implements CacheContract
{

    public function refreshRate(): int
    {
        return 3600;
    }

    public function generate(): array
    {
        $result = [];

        $generateProfile = static function($input, ProfessionType $profession) use (&$result) {
            $profile = &$result[$input['uuid']];
            $profile['name'] = $input['name'];
            $profile['timePlayed'] = $input['minPlayed'];

            $ranks = &$profile['ranks'];
            $ranks[$profession->name] = $input['pos'];
        };

        foreach (ProfessionType::cases() as $enum) {
            $output = self::getLeaderBoard($enum->leaderboard());

            for ($x = 99; $x >= 91; $x--) {
                $generateProfile($output[$x], $enum);
            }
        }

        return $result;
    }

    private static function getLeaderBoard(string $leaderboard): array
    {
        $wynnLeaderboards = WynnRequest::request()->get(config('athena.api.wynn.leaderboards').$leaderboard)->json('data');
        return $wynnLeaderboards ?? [];
    }
}

