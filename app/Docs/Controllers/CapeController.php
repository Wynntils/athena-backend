<?php

namespace App\Docs\Controllers;

use OpenApi\Attributes as OA;

#[
    OA\Get(
        path: "/capes/ban/{Token}/{capeID}",
        operationId: "banCape",
        summary: "Ban cape by Token and capeID",
        tags: ["Cape"],
        parameters: [
            new OA\Parameter(
                name: "Token",
                description: "The cape Token",
                in: "path",
                required: true,
                schema: new OA\Schema(type: "string")
            ),
            new OA\Parameter(
                name: "capeID",
                description: "The cape ID",
                in: "path",
                required: true,
                schema: new OA\Schema(type: "string")
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Successful operation",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: "message",
                            type: "string",
                            example: "The provided cape was banned successfully"
                        ),
                    ]
                )
            ),
            new OA\Response(
                response: 404,
                description: "The provided cape was not found",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: "message",
                            type: "string",
                            example: "There's not a cape with the provided SHA-1"
                        ),
                    ]
                )
            ),
            new OA\Response(
                ref: "#/components/responses/ServerError",
                response: 500
            )
        ]
    ),
    OA\Get(
        path: "/capes/get/{capeID}",
        operationId: "getCape",
        summary: "getCape",
        tags: ["Cape"],
        parameters: [
            new OA\Parameter(
                name: "capeID",
                description: "The cape ID",
                in: "path",
                required: true,
                schema: new OA\Schema(type: "string")
            )
        ],
        responses: [
            new OA\Response(
                ref: "#/components/responses/ServerError",
                response: 500
            )
        ]
    ),
    OA\Get(
        path: "/capes/list",
        operationId: "listCapes",
        summary: "listCapes",
        tags: ["Cape"],
        responses: [
            new OA\Response(
                ref: "#/components/responses/ServerError",
                response: 500
            )
        ]
    ),
    OA\Get(
        path: "/capes/queue/approve/{Token}/{capeID}",
        operationId: "approveCape",
        summary: "approveCape",
        tags: ["Cape"],
        parameters: [
            new OA\Parameter(
                name: "Token",
                description: "The cape Token",
                in: "path",
                required: true,
                schema: new OA\Schema(type: "string")
            ),
            new OA\Parameter(
                name: "capeID",
                description: "The cape ID",
                in: "path",
                required: true,
                schema: new OA\Schema(type: "string")
            )
        ],
        responses: [
            new OA\Response(
                ref: "#/components/responses/ServerError",
                response: 500
            )
        ]
    ),
    OA\Get(
        path: "/capes/queue/get/{capeID}",
        operationId: "getCapeQueue",
        summary: "getCapeQueue",
        tags: ["Cape"],
        parameters: [
            new OA\Parameter(
                name: "capeID",
                description: "The cape ID",
                in: "path",
                required: true,
                schema: new OA\Schema(type: "string")
            )
        ],
        responses: [
            new OA\Response(
                ref: "#/components/responses/ServerError",
                response: 500
            )
        ]
    ),
    OA\Get(
        path: "/capes/queue/list",
        operationId: "listCapeQueue",
        summary: "listCapeQueue",
        tags: ["Cape"],
        responses: [
            new OA\Response(
                ref: "#/components/responses/ServerError",
                response: 500
            )
        ]
    ),
    OA\Post(
        path: "/capes/queue/upload/{Token}",
        operationId: "uploadCape",
        summary: "uploadCape",
        tags: ["Cape"],
        parameters: [
            new OA\Parameter(
                name: "Token",
                description: "The cape Token",
                in: "path",
                required: true,
                schema: new OA\Schema(type: "string")
            )
        ],
        responses: [
            new OA\Response(
                ref: "#/components/responses/ServerError",
                response: 500
            )
        ]
    ),
    OA\Get(
        path: "/capes/user/{UUID}",
        operationId: "userCapes",
        summary: "userCapes",
        tags: ["Cape"],
        parameters: [
            new OA\Parameter(
                name: "UUID",
                description: "The user UUID",
                in: "path",
                required: true,
                schema: new OA\Schema(type: "string")
            )
        ],
        responses: [
            new OA\Response(
                ref: "#/components/responses/ServerError",
                response: 500
            )
        ]
    ),
]
class CapeController
{

}
