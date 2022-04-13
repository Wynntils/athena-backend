<?php

namespace App\Docs\Schema\Cache\Item;

use OpenApi\Attributes as OA;

#[
    OA\Schema(
        title: 'ItemStatusType',
        type: "string",
        enum: [
            "PERCENTAGE",
            "FOUR_SECONDS",
            "TIER",
            "INTEGER",
        ]
    ),
]
class ItemStatusType
{

}
