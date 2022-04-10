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
                ref: "#/components/responses/ServerError",
                response: 500
            )
        ]
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
