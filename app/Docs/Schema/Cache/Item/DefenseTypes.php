<?php

namespace App\Docs\Schema\Cache\Item;

use OpenApi\Attributes as OA;

#[
    OA\Schema(
        title: "Defense Types",
        properties: [
            new OA\Property(
                property: "health",
                type: "integer",
                nullable: true,
            ),
            new OA\Property(
                property: "earth",
                type: "integer",
                nullable: true,
            ),
            new OA\Property(
                property: "thunder",
                type: "integer",
                nullable: true,
            ),
            new OA\Property(
                property: "water",
                type: "integer",
                nullable: true,
            ),
            new OA\Property(
                property: "fire",
                type: "integer",
                nullable: true,
            ),
            new OA\Property(
                property: "air",
                type: "integer",
                nullable: true,
            ),
        ],
        type: "object",
    )
]
class DefenseTypes
{

}
