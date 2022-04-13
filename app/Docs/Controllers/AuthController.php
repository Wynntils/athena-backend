<?php

namespace App\Docs\Controllers;

use App\Docs\OpenAPI;
use OpenApi\Attributes as OA;

#[
    OA\Get(
        path: "/auth/getPublicKey",
        operationId: "getPublicKey",
        summary: "Get public key",
        tags: ["Auth"],
        responses: [
            new OA\Response(
                response: 200,
                description: "Returns Public Key",
                content: new OA\JsonContent(properties: [
                    new OA\Property(property: "publicKeyIn", description: "Public Key", type: "string",
                        example: "308201a2300d06092a864886f70d01010105000382018f003082018a0282018100b0d1a2afb3a11e5b805229e610b627d0a29eeb685e96cadc97046978d753be5ba5211530b364499c89f8a22b27c1dbe9e25ce95a3509d2134fd4f08395d8db7b7c94ca37367176f3bfe60c844ccb784aaf32c87020c1e22445ea9feb8554dddf4a32d7328dccf2197ad78dfbdee583b51004c4f69773b2da05193e901276424855fb90bc89bb938f69137c481a11b1fba6120c008bdd66d4189470dbf8108599756ff1af1e3b3398851ccc9fb8bcde97c72728f8b4b1fbed7ef390cca578cb4e2e9bd0d508ea576456f604122bb9d5bcfddbcc0bfd3ea14f611b8c6d0b75a77b36accab5124ea6f2129cec6e16174ca4c6c64f14458ed8b415f107e39838887a1d15e5b3ff51c80e56154575c0670e7613244cf3d944c09bf626a621cac808d6ad27b3e24ea6b79fd0f9a118f5da50d9dc4de585a8561cc22971c8e1e2326536d359476dee5d37f76ee7cbee4c6316edef5b53a61e17e79f8cf930d1267e780332a5f740b0d6c44a326493a3f6be47d1ba93012b5944088f9d1bd55840e78fd30203010001"),
                ])
            ),
            new OA\Response(ref: OpenAPI::REF_RESPONSE_SERVER_ERROR, response: 500),
        ]
    ),
    OA\Post(
        path: "/auth/responseEncryption",
        operationId: "responseEncryption",
        summary: "Response Encryption",
        requestBody: new OA\RequestBody(
            content: new OA\JsonContent(
                required: ["key", "username", "version"],
                properties: [
                    new OA\Property(property: "username", type: "string", example: "Scyu_"),
                    new OA\Property(property: "key", type: "string",
                        example: "9f26c7e886f07bf05416131b77c5a85d4c63858aafb3540325017b179fc3aebce977a93eedf9161ced492bba497ed07e3440a16741ce9bd36020d408eae9252b438566ae1448560f5d9d91034d663512e4fcb61ae1412635aefc1a4e42284bd2ad33a7bec56e8f881e9858009161f8c3e2643b05ee3b0b6afcc3feac7c79a620014b210d91c0742fe6af24802b6890b4f051d2b1d176f77d16df5df6d7ce4c5fd59fe7f81cde4188b25ee55fe836be3fa3d8203ca2887daafba5df746ad390c492e0d0e7e4511804fa95340a04b3d7915e5466cc126282c6b8e552050d558d668e4ec27611edd20f129182ae01bb517395d8a9d740ae4f177a979f6b2f2050dc2ae1926f9e996bc2d63b7e46b8295671ac04b72dcaf5f0d4199df5797d3816fe6655377f61e4c26842e79c650552f7d839190a9de8fc001f6cda12d882d9df871ab42397b8e9b7b9f16514890b7826742934eb488fdc715c7469f27446d76f02a1345308408e08d9d4944225ee4edee098ab8a0f579847d0868e066f0b70cad1"),
                    new OA\Property(property: "version", type: "string", example: "1.10.5_-1"),
                ]
            )
        ),
        tags: ["Auth"],
        responses: [
            new OA\Response(
                response: 200,
                description: "Returns PublicKey",
                content: new OA\JsonContent(properties: [
                    new OA\Property(property: "message", type: "string", example: "Authentication code generated."),
                    new OA\Property(property: "authToken", type: "string",
                        example: "a8a8f8a8-f8a8-4f8a-a8a8-f8a8a8a8f8a8a"),
                    new OA\Property(property: "configFiles", type: "object"),
                    new OA\Property(property: "hashes", ref: "#/components/schemas/Hashes"),
                ])
            ),
            new OA\Response(
                response: 401,
                description: "Key or username is invalid",
                content: new OA\JsonContent(properties: [
                    new OA\Property(property: "message", type: "string",
                        example: "The provided username or key is invalid"),
                ])
            ),
            new OA\Response(ref: OpenAPI::REF_RESPONSE_BAD_REQUEST, response: 422),
            new OA\Response(ref: OpenAPI::REF_RESPONSE_SERVER_ERROR, response: 500),
        ]
    )
]
class AuthController
{
}
