<?php

namespace App\Docs\Controllers;

use App\Docs\OpenAPI;
use OpenApi\Attributes as OA;

#[
    OA\Get(
        path: "/user/getConfigs",
        operationId: "getUserConfigs",
        summary: "getUserConfigs",
        security: OpenAPI::SECURITY_AUTH_TOKEN,
        tags: ["User"],
        responses: [
            new OA\Response(
                response: 200,
                description: "Success",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "configs", type: "object", example: [
                            "map-overlay_mini_map.config" => "{\"active\":true,\"position\":{\"offsetX\":10,\"offsetY\":10,\"anchorX\":0,\"anchorY\":0}}"
                        ]),
                    ]
                )
            ),
            new OA\Response(
                ref: "#/components/responses/ServerError",
                response: 500
            )
        ]
    ),
    OA\Post(
        path: "/user/getInfo",
        operationId: "postUserInfo",
        summary: "postUserInfo",
        tags: ["User"],
        responses: [
            new OA\Response(
                response: 200,
                description: "Success",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "user", ref: "#/components/schemas/User", type: "object"),
                    ]
                )
            ),
            new OA\Response(
                ref: "#/components/responses/ServerError",
                response: 500
            )
        ],
        deprecated: true
    ),
    OA\Get(
        path: "/user/getInfo/{user}",
        operationId: "getUserInfo",
        summary: "getUserInfo",
        tags: ["User"],
        parameters: [
            new OA\Parameter(
                name: "user",
                in: "path",
                required: true,
                schema: new OA\Schema(type: "string")
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Success",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "user", ref: "#/components/schemas/User", type: "object"),
                    ]
                )
            ),
            new OA\Response(
                ref: "#/components/responses/ServerError",
                response: 500
            )
        ]
    ),
    OA\Post(
        path: "/user/updateDiscord",
        operationId: "updateUserDiscord",
        summary: "updateUserDiscord",
        security: OpenAPI::SECURITY_AUTH_TOKEN,
        requestBody: new OA\RequestBody(
            content: new OA\JsonContent(
                required: ["id", "username"],
                properties: [
                    new OA\Property(property: "id", type: "string"),
                    new OA\Property(property: "username", type: "string"),
                ]
            )
        ),
        tags: ["User"],
        responses: [
            new OA\Response(
                ref: "#/components/responses/ServerError",
                response: 500
            )
        ]
    ),
    OA\Post(
        path: "/user/uploadConfigs",
        operationId: "uploadUserConfigs",
        summary: "uploadUserConfigs",
        security: OpenAPI::SECURITY_AUTH_TOKEN,
        requestBody: new OA\RequestBody(
            content: [
                new OA\MediaType(
                    mediaType: "multipart/form-data",
                    schema: new OA\Schema(
                        properties: [
                            new OA\Property(
                                property: "config",
                                type: "array",
                                items: new OA\Items(
                                    type: "file",
                                    format: "binary"
                                )
                            ),
                        ]
                    )
                )
            ]
        ),
        tags: ["User"],
        responses: [
            new OA\Response(
                ref: "#/components/responses/ServerError",
                response: 500
            )
        ]
    ),

]
class UserController
{
}
