<?php

namespace App\Docs\Schema\Cache;

use OpenApi\Attributes as OA;

#[
    OA\Schema(
        title: 'ServerList',
        properties: [
            new OA\Property(
                property: 'firstSeen',
                description: 'The time the server was first seen.',
                type: 'integer',
                format: 'int64'
            ),
            new OA\Property(
                property: 'players',
                description: 'The players on the server.',
                type: 'array',
                items: new OA\Items(
                    type: 'string'
                ),
                example: [
                    "Player 1",
                    "Player 2",
                    "Player 3",
                    "Player 4",
                ],
            )
        ],
        type: "object",
    )
]
class Server
{

}
