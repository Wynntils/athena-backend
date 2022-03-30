<?php

namespace App\Docs\Schema\Cache\Ingredients;

use OpenApi\Attributes as OA;

/*
'durability'
'duration'
'charges'
'strength'
'dexterity'
'intelligence'
'defense'
'agility'
*/

#[
    OA\Schema(
        title: "ItemModifier",
        properties: [
            new OA\Property(
                property: "durability",
                type: "integer",
                example: -98,
                nullable: true,
            ),
            new OA\Property(
                property: "duration",
                type: "integer",
                example: -98,
                nullable: true,
            ),
            new OA\Property(
                property: "charges",
                type: "integer",
                example: -98,
                nullable: true,
            ),
            new OA\Property(
                property: "strength",
                type: "integer",
                example: -98,
                nullable: true,
            ),
            new OA\Property(
                property: "dexterity",
                type: "integer",
                example: -98,
                nullable: true,
            ),
            new OA\Property(
                property: "intelligence",
                type: "integer",
                example: -98,
                nullable: true,
            ),
            new OA\Property(
                property: "defense",
                type: "integer",
                example: -98,
                nullable: true,
            ),
            new OA\Property(
                property: "agility",
                type: "integer",
                example: -98,
                nullable: true,
            ),
        ],
        type: "object",
    )
]

class ItemModifier
{

}
