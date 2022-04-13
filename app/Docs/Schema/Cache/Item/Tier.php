<?php

namespace App\Docs\Schema\Cache\Item;

use OpenApi\Attributes as OA;

#[
    OA\Schema(
        title: 'Tier',
        type: "string",
        enum: [
            "NORMAL",
            "UNIQUE",
            "RARE",
            "SET",
            "LEGENDARY",
            "MYTHIC",
            "UNIQUE",
        ]
    ),
]
class Tier
{

}
