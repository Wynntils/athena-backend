<?php

namespace App\Docs\Schema\Cache;

use OpenApi\Attributes as OA;

#[
    OA\Schema(
        title: "Leaderboard",
        properties: [
            new OA\Property(
                property: "name",
                description: "The name of the player",
                type: "string"
            ),
            new OA\Property(
                property: "timePlayed",
                description: "The time played of the player",
                type: "integer",
                format: 'int64'
            ),
            new OA\Property(
                property: "ranks",
                ref: "#/components/schemas/LeaderboardRanks",
                description: "The ranks of the player",
                type: "object"
            )
        ],
        type: "object"
    )
]
class Leaderboard
{

}
