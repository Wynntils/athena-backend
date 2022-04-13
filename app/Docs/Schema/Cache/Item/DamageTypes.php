<?php

namespace App\Docs\Schema\Cache\Item;

use OpenApi\Attributes as OA;

#[
    OA\Schema(
        title: "ItemModifier",
        properties: [
            new OA\Property(
                property: "neutral",
                type: "string",
                nullable: true,
            ),
            new OA\Property(
                property: "earth",
                type: "string",
                nullable: true,
            ),
            new OA\Property(
                property: "thunder",
                type: "string",
                nullable: true,
            ),
            new OA\Property(
                property: "water",
                type: "string",
                nullable: true,
            ),
            new OA\Property(
                property: "fire",
                type: "string",
                nullable: true,
            ),
            new OA\Property(
                property: "air",
                type: "string",
                nullable: true,
            ),
        ],
        type: "object",
    )
]
class DamageTypes
{

}
