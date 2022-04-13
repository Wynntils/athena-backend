<?php

namespace App\Docs\Controllers;

use OpenApi\Attributes as OA;

#[
    OA\Get(
        path: "/capes/ban/{capeToken}/{capeID}",
        operationId: "banCape",
        summary: "Ban cape by capeToken and capeID",
        tags: ["Cape"],
        parameters: [
            new OA\Parameter(
                name: "capeToken",
                description: "The cape token",
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
                response: 200,
                description: "Successful operation",
                content: new OA\MediaType(
                    mediaType: "image/png",
                    schema: new OA\Schema(
                        type: "file",
                    )
                )
            ),
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
                response: 200,
                description: "Successful operation",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: "result",
                            type: "array",
                            items: new OA\Items(
                                type: "string",
                                format: "sha1",
                                example: "defaultCape"
                            )
                        )
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
        path: "/capes/queue/approve/{capeToken}/{capeID}",
        operationId: "approveCape",
        summary: "approveCape",
        tags: ["Cape"],
        parameters: [
            new OA\Parameter(
                name: "capeToken",
                description: "The cape token",
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
                            example: "Successfully approved the cape."
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
                response: 200,
                description: "Successful operation",
                content: new OA\MediaType(
                    mediaType: "image/png",
                    schema: new OA\Schema(
                        type: "file",
                    )
                )
            ),
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
        path: "/capes/queue/upload/{capeToken}",
        operationId: "uploadCape",
        summary: "uploadCape",
        tags: ["Cape"],
        parameters: [
            new OA\Parameter(
                name: "capeToken",
                description: "The cape token",
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
                            example: "The cape has been queued for approval."
                        ),
                        new OA\Property(
                            property: "sha-1",
                            type: "string",
                            example: "582915bd8c7bc8f12407cc2615be769fa288bdc4"
                        ),
                    ]
                )
            ),
            new OA\Response(
                response: 400,
                description: "The provided cape is already approved.",
                content: new OA\JsonContent(
                    examples: [
                        new OA\Examples(
                            example: "CapeApproved",
                            summary: "Cape is already approved",
                            value: [
                                "message" => "The provided cape is already approved.",
                            ]
                        ),
                        new OA\Examples(
                            example: "CapeQueued",
                            summary: "Cape is already queued",
                            value: [
                                "message" => "The provided cape is already queued.",
                            ]
                        ),
                        new OA\Examples(
                            example: "CapeBanned",
                            summary: "Cape is already rejected",
                            value: [
                                "message" => "The provided cape is banned.",
                            ]
                        ),
                    ],
                    properties: [
                        new OA\Property(
                            property: "message",
                            type: "string",
                            example: "The provided cape is already approved."
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
                response: 200,
                description: "Successful operation",
                content: new OA\MediaType(
                    mediaType: "image/png",
                    schema: new OA\Schema(
                        type: "file",
                    )
                )
            ),
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
