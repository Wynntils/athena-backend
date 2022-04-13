<?php

namespace App\Docs\Schema\Cache\Item;

use OpenApi\Attributes as OA;

#[
    OA\Schema(
        title: "ItemInfo",
        properties: [
            new OA\Property(
                property: "type",
                type: "string",
                nullable: true,
            ),
            new OA\Property(
                property: "set",
                type: "integer",
                nullable: true,
            ),
            new OA\Property(
                property: "dropType",
                type: "string",
                nullable: true,
            ),
            new OA\Property(
                property: "armorColor",
                type: "integer",
                nullable: true,
            ),
            new OA\Property(
                property: "material",
                type: "string",
                nullable: true,
            ),
        ],
        type: "object",
    )
]
class ItemInfo
{

}
