<?php

namespace App\Docs\Schema\Cache\Territory;

use OpenApi\Attributes as OA;

#[
    OA\Schema(
        title: 'TerritoryLocation',
        description: 'A territory location',
        properties: [
            new OA\Property(property: 'startX', type: 'number'),
            new OA\Property(property: 'startZ', type: 'number'),
            new OA\Property(property: 'endX', type: 'number'),
            new OA\Property(property: 'endZ', type: 'number'),
        ],
        type: 'object',
    )
]
class TerritoryLocation
{

}
