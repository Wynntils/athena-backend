<?php

namespace App\Http\Controllers;

use App\Http\Libraries\CapeManager;
use App\Http\Requests\UserRequest;
use App\Models\User;
use Auth;

class UserController extends Controller
{
    public function updateDiscord(UserRequest $request): \Illuminate\Http\JsonResponse
    {
        \Auth::user()?->updateDiscord($request->validated('id'), $request->validated('username'));
        return response()->json(['message' => 'Success'], 200);
    }

    public function uploadConfigs(UserRequest $request)
    {
        Auth::user()?->uploadConfig($request->validated('config'));
        return response()->json(['message' => 'Successfully uploaded config.'], 200);
    }

    public function getConfigs(): \Illuminate\Http\JsonResponse
    {
        return response()->json(['configs' => Auth::user()?->getConfigs()], 200);
    }

    public function getInfo(User $user): \Illuminate\Http\JsonResponse
    {
        return response()->json([
            'user' => [
                'accountType' => $user->authToken,
                'cosmetics' => [
                    'hasCape' => $user->cosmeticInfo->hasCape(),
                    'hasElytra' => $user->cosmeticInfo->hasElytra(),
                    'hasEars' => $user->cosmeticInfo->hasPart("ears"),
                    'texture' => CapeManager::instance()->getCapeAsBase64($user->cosmeticInfo->getFormattedTexture())
                ]
            ]
        ]);
    }

    /**
     * @deprecated
     */
    public function getInfoLegacy(UserRequest $request): \Illuminate\Http\JsonResponse
    {
        $user = User::findOrFail($request->validated('uuid'));
        return response()->json([
            'user' => [
                'accountType' => $user->authToken,
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
