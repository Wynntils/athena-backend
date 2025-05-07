<?php

namespace App\Http\Controllers;

use App\Http\Libraries\CapeManager;
use App\Http\Requests\UserRequest;
use App\Models\User;
use Auth;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;

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

            $user->uploadConfig($config);
            $fileResult['message'] = 'Configuration stored successfully';
        }

        return response()->json($result, 200);
    }

    public function getConfigs(): \Illuminate\Http\JsonResponse
    {
        return response()->json(['configs' => Auth::user()?->getConfigs()], 200);
    }

    public function getInfo($user): \Illuminate\Http\JsonResponse
    {
        $user = $this->getUser($user);

        return response()->json([
            'user' => [
                'uuid' => $user->id,
                'username' => $user->username,
                'accountType' => $user->accountType,
                'cosmetics' => [
                    'hasCape' => $user->cosmetics?->capeStyle === "cape" ? true : false,
                    'hasElytra' => $user->cosmetics?->capeStyle === "elytra" ? true : false,
                    'hasEars' => false, //-- Ears are not implemented in the 1.21.4 versions yet.
                    'texture' => $user->cosmetics?->cape?->texture,
                ]
            ]
        ]);
    }

    public function getInfoV2(UserRequest $request): \Illuminate\Http\JsonResponse
    {
        $user = $this->getUser($request->validated('uuid'));

        $cosmetics = $request->query('cosmetics', false);
        $cosmetics = filter_var($cosmetics, FILTER_VALIDATE_BOOLEAN);

        $response = [
            'uuid' => $user->id,
            'username' => $user->username,
            'accountType' => $user->accountType,
        ];

        // Conditionally add cosmetics info
        if ($cosmetics) {


            $texture = CapeManager::instance()->getCapeAsBase64($user->cosmeticInfo?->getFormattedTexture() ?? '', true);
            $response['cosmetics'] = [
                'hasCape' => $user->cosmeticInfo?->hasCape() ?? false,
                'hasElytra' => $user->cosmeticInfo?->hasElytra() ?? false,
                'hasEars' => $user->cosmeticInfo?->hasPart("ears") ?? false,
                'texture' => $texture
            ];
        }

        return response()->json(['user' => $response]);
    }

    public function getInfoPost(UserRequest $request): \Illuminate\Http\JsonResponse
    {
        $user = $this->getUser($request->validated('uuid'));

        return response()->json([
            'user' => [
                'accountType' => $user->accountType,
                'cosmetics' => [
                    'hasCape' => $user->cosmeticInfo?->hasCape() ?? false,
                    'hasElytra' => $user->cosmeticInfo?->hasElytra() ?? false,
                    'hasEars' => $user->cosmeticInfo?->hasPart("ears") ?? false,
                    'texture' => CapeManager::instance()->getCapeAsBase64($user->cosmeticInfo?->getFormattedTexture() ?? '', true)
                ]
            ]
        ]);
    }

    private function getUser($user) {
        $user = Cache::remember("user-{$user}", 3600, function () use ($user) {
            return User::where('_id', $user)->first();
        });


        if (!$user) {
            response()->json(['error' => 'User not found'], 404)->throwResponse();
        }

        return $user;
    }
}
