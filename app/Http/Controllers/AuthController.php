<?php

namespace App\Http\Controllers;

use App\Enums\AccountType;
use App\Events\LoginEvent;
use App\Events\SignUpEvent;
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
            [$profile, $sharedKey, $publicKey, $serverId] = MinecraftFakeAuth::instance()->getGameProfile($request->validated('username'), $request->validated('key'));

            // Store request, profile and serverId
            Storage::put('request.json', $request->getContent() . "\n\n" . json_encode($profile, JSON_PRETTY_PRINT) . "\n\n" . base64_encode($sharedKey) . "\n\n" . base64_encode($publicKey) . "\n\n" . $serverId);
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

        $user = User::where('_id', Uuid::fromString($profile['id'])->toString())->first();

        if ($user === null) {
            $user = User::create([
                '_id' => Uuid::fromString($profile['id'])->toString(),
                'accountType' => AccountType::NORMAL
            ]);

            // Fire SignUp Event
            SignUpEvent::dispatch($user, 'Minecraft');
        } else {
            // Fire Login Event
            LoginEvent::dispatch($user, 'Minecraft');
        }

        $user->updateAccount($profile['name'], $request->validated('version'));

        $response = [];
        $response['message'] = "Authentication code generated.";
        $response['authToken'] = $user->authToken;
        $response['configFiles'] = $user->getConfigs();
        $response['hashes'] = CacheManager::getHashes();

        return response()->json($response);
    }
}
