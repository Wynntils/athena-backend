<?php

namespace App\Docs\Schema\Cache\Item;

use OpenApi\Attributes as OA;

#[
    OA\Schema(
        title: "Requirements",
        properties: [
            new OA\Property(
                property: "quest",
                type: "string",
                nullable: true,
            ),
            new OA\Property(
                property: "classType",
                type: "string",
                nullable: true,
            ),
            new OA\Property(
                property: "level",
                type: "integer",
                nullable: true,
            ),
            new OA\Property(
                property: "strength",
                type: "integer",
                nullable: true,
            ),
            new OA\Property(
                property: "dexterity",
                type: "integer",
                nullable: true,
            ),
            new OA\Property(
                property: "intelligence",
                type: "integer",
                nullable: true,
            ),
            new OA\Property(
                property: "defense",
                type: "integer",
                nullable: true,
            ),
            new OA\Property(
                property: "agility",
                type: "integer",
                nullable: true,
            ),
        ],
        type: "object",
    )
]
class Requirements
{

}
