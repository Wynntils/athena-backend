<?php

namespace App\Docs\Schema\Cache\Item;

use OpenApi\Attributes as OA;

#[
    OA\Schema(
        title: 'ItemStatus',
        properties: [
            new OA\Property(
                property: 'type',
                ref: '#/components/schemas/ItemStatusType',
            ),
            new OA\Property(
                property: 'isFixed',
                type: 'boolean',
            ),
            new OA\Property(
                property: 'baseValue',
                type: 'integer',
            ),
        ],
        type: "object"
    ),
]
class ItemStatus
{

}
