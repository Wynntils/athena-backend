<?php

namespace App\Http\Controllers;

use App\Http\Libraries\CapeManager;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function updateDiscord(Request $request): \Illuminate\Http\JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'authToken' => 'required|uuid|exists:App\Models\User,authToken',
            'id' => 'required|int',
            'username' => 'required|string'
        ]);
        if($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        $body = $request->post();
        $user = User::where(['authToken' => $body['authToken']])->firstOrFail();
        $user->updateDiscord($body['id'], $body['username']);
        return response()->json(['message' => 'Successfully updated '.$user->username.' Discord Information.'], 200);
    }

    public function uploadConfigs(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'authToken' => 'required|uuid|exists:App\Models\User,authToken',
            'config' => 'required|file'
        ]);
        if($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }
        $body = $request->post();
        $user = User::where(['authToken' => $body['authToken']])->firstOrFail();

        $config = $request->file('config');
        $content = zlib_encode($config->getContent(), ZLIB_ENCODING_DEFLATE);
        if(mb_strlen($content, 'utf-8') > 200) { // ?
            return response()->json(['message' => 'The provided configuration is bigger than 200kb.']);
        }
        if($user->getConfigAmount() >= 80) {
            return response()->json(['message' => 'User exceeded the configuration amount limit.']);
        }

        $user->setConfig($config->getClientOriginalName(), $content);

        return response()->json(['message' => 'Configuration stored successfully.']);
    }

    public function getInfo(Request $request): \Illuminate\Http\JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'uuid' => 'required|exists:App\Models\User,id',
        ]);
        if($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }
        $user = User::find($request->json('uuid'));
        return response()->json([
            'user' => [
                'accountType' => $user->accountType,
                'cosmetics' => [
                    'hasCape' => $user->cosmeticInfo->hasCape(),
                    'hasElytra' => $user->cosmeticInfo->hasElytra(),
                    'hasEars' => $user->cosmeticInfo->hasPart("ears"),
                    'texture' => CapeManager::instance()->getCapeAsBase64($user->cosmeticInfo->getFormattedTexture())
                ]
            ]
        ]);
    }
}
