<?php

namespace App\Docs\Schema\Cache\Ingredients;

use OpenApi\Attributes as OA;

/*
'left'
'right'
'above'
'under'
'touching'
'notTouching'
 */


#[
    OA\Schema(
        title: "IngredientModifier",
        properties: [
            new OA\Property(
                property: "left",
                type: "integer",
                example: 5,
                nullable: true,
            ),
            new OA\Property(
                property: "right",
                type: "integer",
                example: 5,
                nullable: true,
            ),
            new OA\Property(
                property: "above",
                type: "integer",
                example: 5,
                nullable: true,
            ),
            new OA\Property(
                property: "under",
                type: "integer",
                example: 5,
                nullable: true,
            ),
            new OA\Property(
                property: "touching",
                type: "integer",
                example: 5,
                nullable: true,
            ),
            new OA\Property(
                property: "notTouching",
                type: "integer",
                example: 5,
                nullable: true,
            ),
        ],
        type: "object",
    )
]

class IngredientModifier
{

}
