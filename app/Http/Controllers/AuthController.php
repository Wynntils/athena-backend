<?php

namespace App\Http\Controllers;

use App\Http\Libraries\MinecraftFakeAuth;
use App\Models\User;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function getPublicKey(): \Illuminate\Http\JsonResponse
    {
        return response()->json(['publicKeyIn' => bin2hex(MinecraftFakeAuth::instance()->getPublicKey())]);
    }

    public function responseEncryption(Request $request): \Illuminate\Http\JsonResponse
    {
        if(config('app.debug') !== false) {
            // Useful for debugging requests
            \Storage::put('request.json', $request->getContent());
        }
        $body = $request->json();

        if(!$body->has('username') || !$body->has('key') || !$body->has('version')) {
            return response()->json(['message' => "Expecting parameters 'username', 'key' and 'version'."], 400);
        }

        $profile = MinecraftFakeAuth::instance()->getGameProfile($body->get('username'), $body->get('key'));

        if ($profile === null) {
            return response()->json(['message' => 'The provided username or key is invalid'], 401);
        }
        $uuid = preg_replace("/(\w{8})(\w{4})(\w{4})(\w{4})(\w{12})/i", "$1-$2-$3-$4-$5", $profile['id']);

        $user = User::find($uuid);

        $user->updateAccount($profile['name'], $body->get('version'));

        $response = [];
        $response['message'] = "Authentication code generated.";
        $response['authToken'] = $user->authToken;
        $response['configFiles'] = $user->getConfigFiles();
        $response['hashes'] = new \ArrayObject();

        /* TODO configFiles and hashes
        val hashes = response.getOrCreate<JSONObject>("hashes")
        for (entry in CacheManager.getCaches()) {
            hashes[entry.key] = entry.value.hash
        }
         */

        return response()->json($response);
    }
}
