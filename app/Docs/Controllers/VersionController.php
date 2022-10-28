<?php

namespace App\Docs\Controllers;

use App\Docs\OpenAPI;
use OpenApi\Attributes as OA;

#[
    OA\Get(
        path: "/version/latest/{stream}",
        operationId: "getVersionLatest",
        summary: "Get the latest version for a stream",
        tags: ["Version"],
        parameters: [
            new OA\Parameter(
                name: "stream",
                description: "The stream to get the latest version for",
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
                        new OA\Property(property: "version", type: "string"),
                        new OA\Property(property: "url", type: "string"),
                        new OA\Property(property: "md5", type: "string"),
                        new OA\Property(property: "changelog", type: "string"),
                    ]
                )
            ),
            new OA\Response(ref: OpenAPI::REF_RESPONSE_UNAUTHORIZED, response: 401),
            new OA\Response(ref: OpenAPI::REF_RESPONSE_SERVER_ERROR, response: 500)
        ]
    ),
    OA\Get(
        path: "/version/changelog/{version}",
        operationId: "getVersionChangelog",
        summary: "Get the changelog for a version",
        tags: ["Version"],
        parameters: [
            new OA\Parameter(
                name: "version",
                description: "The version to get the changelog for",
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
                        new OA\Property(property: "version", type: "string"),
                        new OA\Property(property: "changelog", type: "string"),
                    ]
                )
            ),
            new OA\Response(ref: OpenAPI::REF_RESPONSE_UNAUTHORIZED, response: 401),
            new OA\Response(ref: OpenAPI::REF_RESPONSE_SERVER_ERROR, response: 500)
        ]
    )
]
class VersionController
{
}
