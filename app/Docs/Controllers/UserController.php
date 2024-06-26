<?php

namespace App\Docs\Controllers;

use App\Docs\OpenAPI;
use OpenApi\Attributes as OA;

#[
    OA\Get(
        path: "/user/getConfigs",
        operationId: "getUserConfigs",
        summary: "Get user configs",
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
            new OA\Response(ref: OpenAPI::REF_RESPONSE_UNAUTHORIZED, response: 401),
            new OA\Response(ref: OpenAPI::REF_RESPONSE_SERVER_ERROR, response: 500)
        ]
    ),
    OA\Post(
        path: "/user/getInfo",
        operationId: "postUserInfo",
        summary: "Get user info",
        requestBody: new OA\RequestBody(
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "uuid", type: "string", example: "123e4567-e89b-12d3-a456-426655440000"),
                ]
            )
        ),
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
            new OA\Response(ref: OpenAPI::REF_RESPONSE_UNAUTHORIZED, response: 401),
            new OA\Response(ref: OpenAPI::REF_RESPONSE_SERVER_ERROR, response: 500)
        ],
    ),
    OA\Post(
        path: "/v2/user/getInfo",
        operationId: "postUserInfoV2",
        summary: "Get user info v2",
        requestBody: new OA\RequestBody(
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "uuid", type: "string", example: "123e4567-e89b-12d3-a456-426655440000"),
                ]
            )
        ),
        tags: ["User"],
        parameters: [
            new OA\Parameter(
                name: "cosmetics",
                in: "query",
                required: false,
                schema: new OA\Schema(type: "string"),
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
            new OA\Response(ref: OpenAPI::REF_RESPONSE_UNAUTHORIZED, response: 401),
            new OA\Response(ref: OpenAPI::REF_RESPONSE_SERVER_ERROR, response: 500)
        ],
    ),
    OA\Get(
        path: "/user/getInfo/{user}",
        operationId: "getUserInfo",
        summary: "Get user info",
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
            new OA\Response(ref: OpenAPI::REF_RESPONSE_SERVER_ERROR, response: 500)
        ]
    ),
    OA\Post(
        path: "/user/updateDiscord",
        operationId: "updateUserDiscord",
        summary: "Update user discord",
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
                response: 200,
                description: "Success",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "message", type: "string", example: "Success"),
                    ]
                )
            ),
            new OA\Response(ref: OpenAPI::REF_RESPONSE_BAD_REQUEST, response: 422),
            new OA\Response(ref: OpenAPI::REF_RESPONSE_UNAUTHORIZED, response: 401),
            new OA\Response(ref: OpenAPI::REF_RESPONSE_SERVER_ERROR, response: 500)
        ]
    ),
    OA\Post(
        path: "/user/uploadConfigs",
        operationId: "uploadUserConfigs",
        summary: "Upload user configs",
        security: OpenAPI::SECURITY_AUTH_TOKEN,
        requestBody: new OA\RequestBody(
            content: [
                new OA\MediaType(
                    mediaType: "multipart/form-data",
                    schema: new OA\Schema(
                        required: ["config"],
                        properties: [
                            new OA\Property(
                                property: "config",
                                type: "array",
                                items: new OA\Items(
                                    type: "string",
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
                response: 200,
                description: "Success",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: "results",
                            type: "array",
                            items: new OA\Items(
                                properties: [
                                    new OA\Property(property: "name", type: "string", example: "map-overlay_mini_map.config"),
                                    new OA\Property(property: "message", type: "string", example: "Configuration stored successfully"),
                                ],
                                type: "object"
                            )
                        ),
                    ]
                )
            ),
            new OA\Response(ref: OpenAPI::REF_RESPONSE_BAD_REQUEST, response: 422),
            new OA\Response(ref: OpenAPI::REF_RESPONSE_UNAUTHORIZED, response: 401),
            new OA\Response(ref: OpenAPI::REF_RESPONSE_SERVER_ERROR, response: 500)
        ]
    ),

]
class UserController
{
}
