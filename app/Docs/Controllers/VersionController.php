<?php

namespace App\Docs\Controllers;

use App\Docs\OpenAPI;
use OpenApi\Attributes as OA;

#[
    OA\Get(
        path: "/version/latest/{stream}",
        operationId: "getVersionLatest",
        description: "Get latest version for a stream",
        summary: "Get latest version",
        tags: ["Version"],
        parameters: [
            new OA\Parameter(
                name: "stream",
                description: "The stream to get the latest version for",
                in: "path",
                required: true,
                schema: new OA\Schema(description: "The stream to get the latest version for", type: "string", enum: ["ce", "re"], example: "ce"),
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Success",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "version", description: "The latest version for the stream", type: "string", example: "v1.13.1"),
                        new OA\Property(property: "url", description: "The download URL for the latest version", type: "string", example: "https://github.com/Wynntils/Wynntils/releases/download/v1.13.1/Wynntils-MC1.12.2-v1.13.1.jar"),
                        new OA\Property(property: "md5", description: "The MD5 hash of the latest version", type: "string", example: "a535b6431f024e3dd49fed963f32655d"),
                        new OA\Property(property: "changelog", description: "The URL to the changelog for the latest version", type: "string", example: "https://athena.wynntils.com/version/changelog/v1.13.1"),
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
        description: "Get the changelog for a version",
        summary: "Get changelog",
        tags: ["Version"],
        parameters: [
            new OA\Parameter(
                name: "version",
                description: "The version to get the changelog for",
                in: "path",
                required: true,
                schema: new OA\Schema(description: "The version to get the changelog for", type: "string", example: "v1.13.0"),
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Success",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "version", description: "The version the changelog is for", type: "string", example: "v1.13.1"),
                        new OA\Property(property: "changelog", description: "The changelog for the version in markdown format", type: "string", example: "### Bug Fixes\\n\\n* Guild territory list occasionally causing NPE (#618) \\n* Missing Arrow Bomb cost modifiers (#617) \\n* Changelog and Updater bugfixing (#613) \\n"),
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
