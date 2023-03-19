<?php

namespace App\Http\Controllers;

use App\Enums\AccountType;
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

        try {
            $profile = MinecraftFakeAuth::instance()->getGameProfile($request->validated('username'), $request->validated('key'));
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }

        if ($profile === null) {
            return response()->json(['message' => 'The provided username or key is invalid'], 401);
        }

        if (!array_key_exists('id', $profile)) {
//            Notifications::log('<@&980223126619176960>', "Unknown Profile for `{$request->validated('username')}`", "```json\n" . json_encode($profile, JSON_PRETTY_PRINT) . '```');
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
