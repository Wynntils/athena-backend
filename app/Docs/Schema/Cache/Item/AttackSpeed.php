<?php

namespace App\Docs\Schema\Cache\Item;

use OpenApi\Attributes as OA;

#[
    OA\Schema(
        title: 'AttackSpeed',
        type: "string",
        enum: [
            "SUPER_SLOW", "VERY_SLOW", "SLOW", "NORMAL", "FAST", "VERY_FAST", "SUPER_FAST"
        ]
    ),
]
class AttackSpeed
{

}
