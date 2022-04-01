<?php

namespace App\Http\Controllers;

use App\Http\Enums\AccountType;
use App\Http\Libraries\CacheManager;
use App\Http\Libraries\MinecraftFakeAuth;
use App\Http\Requests\AuthRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Ramsey\Uuid\Uuid;
use Storage;

class AuthController extends Controller
{
    public function getPublicKey(): JsonResponse
    {
        return response()->json(['publicKeyIn' => bin2hex(MinecraftFakeAuth::instance()->getPublicKey())]);
    }

    public function responseEncryption(AuthRequest $request): JsonResponse
    {
        if (config('app.debug') !== false) {
            // Useful for debugging requests
            Storage::put('request.json', $request->getContent());
        }

        // $profile = MinecraftFakeAuth::instance()->getGameProfile($request->validated('username'), $request->validated('key'));

        $profile = [
            'id' => 'af4addfc-c030-4fe5-a2d6-1061e8c96386',
            'name' => 'Syaoran3'
        ];

        if ($profile === null) {
            return response()->json(['message' => 'The provided username or key is invalid'], 401);
        }

        $user = User::firstOrCreate(['_id' => Uuid::fromString($profile['id'])->toString()], ['accountType' => AccountType::NORMAL]);

        $user->updateAccount($profile['name'], $request->validated('version'));

        $response = [];
        $response['message'] = "Authentication code generated.";
        $response['authToken'] = $user->authToken;
        $response['configFiles'] = $user->getConfigs();
        $response['hashes'] = CacheManager::getHashes();

        return response()->json($response);
    }
}
