<?php

namespace App\Http\Controllers;

use App\Http\Libraries\CapeManager;
use App\Models\User;
use Illuminate\Database\DatabaseManager;
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
        // TODO: uploadConfigs function
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
