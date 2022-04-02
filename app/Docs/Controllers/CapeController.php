<?php

namespace App\Docs\Controllers;

use OpenApi\Attributes as OA;

#[
    OA\Get(
        path: "/capes/ban/{token}/{sha}",
        operationId: "banCape",
        summary: "Ban cape by token and sha",
        tags: ["Cape"],
        parameters: [
            new OA\Parameter(
                name: "token",
                description: "The cape token",
                in: "path",
                required: true,
                schema: new OA\Schema(type: "string")
            ),
            new OA\Parameter(
                name: "sha",
                description: "The cape sha",
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
        path: "/capes/get/{id}",
        operationId: "getCape",
        summary: "getCape",
        tags: ["Cape"],
        parameters: [
            new OA\Parameter(
                name: "id",
                description: "The cape id",
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
        path: "/capes/queue/approve/{token}/{sha}",
        operationId: "approveCape",
        summary: "approveCape",
        tags: ["Cape"],
        parameters: [
            new OA\Parameter(
                name: "token",
                description: "The cape token",
                in: "path",
                required: true,
                schema: new OA\Schema(type: "string")
            ),
            new OA\Parameter(
                name: "sha",
                description: "The cape sha",
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
        path: "/capes/queue/get/{id}",
        operationId: "getCapeQueue",
        summary: "getCapeQueue",
        tags: ["Cape"],
        parameters: [
            new OA\Parameter(
                name: "id",
                description: "The cape id",
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
        path: "/capes/queue/upload/{token}",
        operationId: "uploadCape",
        summary: "uploadCape",
        tags: ["Cape"],
        parameters: [
            new OA\Parameter(
                name: "token",
                description: "The cape token",
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
        path: "/capes/user/{uuid}",
        operationId: "userCapes",
        summary: "userCapes",
        tags: ["Cape"],
        parameters: [
            new OA\Parameter(
                name: "uuid",
                description: "The user uuid",
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
