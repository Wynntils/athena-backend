<?php

namespace App\Docs\Schema\Cache\Leaderboard;

use OpenApi\Attributes as OA;

#[
    OA\Schema(
        properties: [
            new OA\Property(
                property: "WOODCUTTING",
                description: "The rank of the player in the Woodcutting skill",
                type: "integer",
                nullable: true,
            ),
            new OA\Property(
                property: "MINING",
                description: "The rank of the player in the Mining skill",
                type: "integer",
                nullable: true,
            ),
            new OA\Property(
                property: "FISHING",
                description: "The rank of the player in the Fishing skill",
                type: "integer",
                nullable: true,
            ),
            new OA\Property(
                property: "FARMING",
                description: "The rank of the player in the Farming skill",
                type: "integer",
                nullable: true,
            ),
            new OA\Property(
                property: "ALCHEMISM",
                description: "The rank of the player in the Alchemy skill",
                type: "integer",
                nullable: true,
            ),
            new OA\Property(
                property: "ARMOURING",
                description: "The rank of the player in the Armouring skill",
                type: "integer",
                nullable: true,
            ),
            new OA\Property(
                property: "COOKING",
                description: "The rank of the player in the Cooking skill",
                type: "integer",
                nullable: true,
            ),
            new OA\Property(
                property: "JEWELING",
                description: "The rank of the player in the Jewelry skill",
                type: "integer",
                nullable: true,
            ),
            new OA\Property(
                property: "SCRIBING",
                description: "The rank of the player in the Scribing skill",
                type: "integer",
                nullable: true,
            ),
            new OA\Property(
                property: "TAILORING",
                description: "The rank of the player in the Tailoring skill",
                type: "integer",
                nullable: true,
            ),
            new OA\Property(
                property: "WEAPONSMITHING",
                description: "The rank of the player in the Weapon Smithing skill",
                type: "integer",
                nullable: true,
            ),
            new OA\Property(
                property: "WOODWORKING",
                description: "The rank of the player in the Woodworking skill",
                type: "integer",
                nullable: true,
            ),
        ]
    )
]
class LeaderboardRanks
{

}
