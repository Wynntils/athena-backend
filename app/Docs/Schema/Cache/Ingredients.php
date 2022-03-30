<?php

namespace App\Docs\Schema\Cache;

use OpenApi\Attributes as OA;

#[
    OA\Schema(
        title: 'Ingredients',
        description: 'A list of ingredients',
        properties: [
            new OA\Property(
                property: "name",
                type: "string",
                example: "7-Yottabyte Storage Component",
            ),
            new OA\Property(
                property: "tier",
                type: "number",
                example: 2,
            ),
            new OA\Property(
                property: "level",
                type: "number",
                example: 90,
            ),
            new OA\Property(
                property: "untradeable",
                type: "boolean",
                example: false,
            ),
            new OA\Property(
                property: "material",
                type: "string",
                example: "356:0",
            ),
            new OA\Property(
                property: "professions",
                type: "array",
                items: new OA\Items(
                    ref: "#/components/schemas/Profession",
                ),
                example: [
                    "FISHING",
                    "FARMING",
                    "ALCHEMISM",
                ]
            ),
            new OA\Property(
                property: "itemModifiers",
                ref: "#/components/schemas/ItemModifier",
            ),
            new OA\Property(
                property: "ingredientModifiers",
                ref: "#/components/schemas/IngredientModifier",
            ),
        ]
    )
]

class Ingredients
{

}
