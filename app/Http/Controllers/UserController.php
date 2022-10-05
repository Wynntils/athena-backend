<?php

namespace App\Http\Controllers;

use App\Http\Libraries\CapeManager;
use App\Http\Requests\UserRequest;
use App\Models\User;
use Auth;
use Illuminate\Http\UploadedFile;

class UserController extends Controller
{
    public function updateDiscord(UserRequest $request): \Illuminate\Http\JsonResponse
    {
        \Auth::user()?->updateDiscord($request->validated('id'), $request->validated('username'));
        return response()->json(['message' => 'Success'], 200);
    }

    public function uploadConfigs(UserRequest $request): \Illuminate\Http\JsonResponse
    {
        $result = $uploadResult = [];
        $result['results'] = &$uploadResult;

        /** @var \App\Models\User $user */
        $user = \Auth::user();
        /** @var UploadedFile $config */
        foreach($request->validated('config') as $config) {
            $fileResult = [];
            $uploadResult[] = &$fileResult;
            $fileResult['name'] = $config->getClientOriginalName();

            if($user->getConfigAmount() >= 80) {
                $fileResult['message'] = 'User exceeded the configuration amount limit.';
                continue;
            }

            $user->uploadConfig($config);
            $fileResult['message'] = 'Configuration stored successfully';
        }

        return response()->json($result, 200);
    }

    public function getConfigs(): \Illuminate\Http\JsonResponse
    {
        return response()->json(['configs' => Auth::user()?->getConfigs()], 200);
    }

    public function getInfo(User $user): \Illuminate\Http\JsonResponse
    {
        return response()->json([
            'user' => [
                'uuid' => $user->id,
                'username' => $user->username,
                'accountType' => $user->accountType,
                'cosmetics' => [
                    'hasCape' => $user->cosmeticInfo?->hasCape() ?? false,
                    'hasElytra' => $user->cosmeticInfo?->hasElytra() ?? false,
                    'hasEars' => $user->cosmeticInfo?->hasPart("ears") ?? false,
                    'texture' => CapeManager::instance()->getCapeAsBase64($user->cosmeticInfo?->getFormattedTexture() ?? '')
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
                'accountType' => $user->accountType,
                'cosmetics' => [
                    'hasCape' => $user->cosmeticInfo?->hasCape() ?? false,
                    'hasElytra' => $user->cosmeticInfo?->hasElytra() ?? false,
                    'hasEars' => $user->cosmeticInfo?->hasPart("ears") ?? false,
                    'texture' => CapeManager::instance()->getCapeAsBase64($user->cosmeticInfo?->getFormattedTexture() ?? '')
                ]
            ]
        ]);
    }
}
