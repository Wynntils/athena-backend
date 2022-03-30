<?php

namespace App\Docs\Schema\Cache;

use OpenApi\Attributes as OA;

#[
    OA\Schema(
        title: "Hashes",
        properties: [
            new OA\Property(
                property: "gatheringSpots",
                type: "string",
                example: "f7a4086c9955b632ddda0c89734d5af0fb3e2e70",
                nullable: true
            ),
            new OA\Property(
                property: "ingredientList",
                type: "string",
                example: "f7a4086c9955b632ddda0c89734d5af0fb3e2e70",
                nullable: true
            ),
            new OA\Property(
                property: "itemList",
                type: "string",
                example: "f7a4086c9955b632ddda0c89734d5af0fb3e2e70",
                nullable: true
            ),
            new OA\Property(
                property: "leaderboard",
                type: "string",
                example: "f7a4086c9955b632ddda0c89734d5af0fb3e2e70",
                nullable: true
            ),
            new OA\Property(
                property: "mapLocations",
                type: "string",
                example: "f7a4086c9955b632ddda0c89734d5af0fb3e2e70",
                nullable: true
            ),
            new OA\Property(
                property: "serverList",
                type: "string",
                example: "f7a4086c9955b632ddda0c89734d5af0fb3e2e70",
                nullable: true
            ),
            new OA\Property(
                property: "territoryList",
                type: "string",
                example: "f7a4086c9955b632ddda0c89734d5af0fb3e2e70",
                nullable: true
            ),
        ]
    ),
]
class Hashes
{

}
