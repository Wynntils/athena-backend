<?php

namespace App\Http\Controllers;

use App\Http\Libraries\CacheManager;
use App\Http\Libraries\MinecraftFakeAuth;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Ramsey\Uuid\Uuid;
use Storage;

class AuthController extends Controller
{
    public function getPublicKey(): JsonResponse
    {
        return response()->json(['publicKeyIn' => bin2hex(MinecraftFakeAuth::instance()->getPublicKey())]);
    }

    public function responseEncryption(Request $request): JsonResponse
    {
        if (config('app.debug') !== false) {
            // Useful for debugging requests
            Storage::put('request.json', $request->getContent());
        }
        $body = $request->json();

        if (!$body->has('username') || !$body->has('key') || !$body->has('version')) {
            return response()->json(['message' => "Expecting parameters 'username', 'key' and 'version'."], 400);
        }

        $profile = MinecraftFakeAuth::instance()->getGameProfile($body->get('username'), $body->get('key'));

        if ($profile === null) {
            return response()->json(['message' => 'The provided username or key is invalid'], 401);
        }

        $user = User::find(Uuid::fromString($profile['id'])->toString());

        $user->updateAccount($profile['name'], $body->get('version'));

        $response = [];
        $response['message'] = "Authentication code generated.";
        $response['authToken'] = $user->authToken;
        $response['configFiles'] = $user->getConfigFiles();
        $response['hashes'] = CacheManager::getHashes();

        return response()->json($response);
    }
}
