<?php

namespace App\Http\Controllers;

use App\Http\Libraries\CapeManager;
use App\Http\Requests\UserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Auth;
use Dedoc\Scramble\Attributes\ExcludeRouteFromDocs;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;

#[Group('User')]
class UserController extends Controller
{
    /**
     * Link a Discord account to the authenticated user
     */
    public function updateDiscord(UserRequest $request): JsonResponse
    {
        \Auth::user()?->updateDiscord($request->validated('id'), $request->validated('username'));

        return response()->json(['message' => 'Success'], 200);
    }

    /**
     * Upload configuration files for the authenticated user
     */
    public function uploadConfigs(UserRequest $request): JsonResponse
    {
        $result = $uploadResult = [];
        $result['results'] = &$uploadResult;

        /** @var \App\Models\User $user */
        $user = \Auth::user();
        /** @var UploadedFile $config */
        foreach ($request->validated('config') as $config) {
            $fileResult = [];
            $uploadResult[] = &$fileResult;
            $fileResult['name'] = $config->getClientOriginalName();

            $user->uploadConfig($config);
            $fileResult['message'] = 'Configuration stored successfully';
        }

        return response()->json($result, 200);
    }

    #[ExcludeRouteFromDocs]
    public function getConfigs(): JsonResponse
    {
        return response()->json(['configs' => Auth::user()?->getConfigs()], 200);
    }

    #[ExcludeRouteFromDocs]
    public function getInfo($user): JsonResponse
    {
        $user = $this->getUser($user);

        return response()->json([
            'user' => [
                'uuid' => $user->id,
                'username' => $user->username,
                'accountType' => $user->accountType,
                'cosmetics' => [
                    'hasCape' => $user->hasCape(),
                    'hasElytra' => $user->hasElytra(),
                    'hasEars' => $user->hasPart('ears'),
                    'texture' => CapeManager::instance()->getCapeAsBase64($user->getFormattedTexture(), true),
                ],
            ],
        ]);
    }

    #[ExcludeRouteFromDocs]
    public function getInfoV2(UserRequest $request): JsonResponse
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

            $texture = CapeManager::instance()->getCapeAsBase64($user->getFormattedTexture(), true);
            $response['cosmetics'] = [
                'hasCape' => $user->hasCape(),
                'hasElytra' => $user->hasElytra(),
                'hasEars' => $user->hasPart('ears'),
                'texture' => $texture,
            ];
        }

        return response()->json(['user' => $response]);
    }

    /**
     * Get user account type and cosmetics
     */
    public function getInfoPost(UserRequest $request): UserResource|\Illuminate\Http\JsonResponse|\Illuminate\Http\Response
    {
        $user = $this->getUser($request->validated('uuid'));

        $etag = md5($user->account_type->value.json_encode($user->cosmetic_info));

        if ($request->header('If-None-Match') === $etag) {
            return response('', 304);
        }

        return (new UserResource($user))->response()->header('ETag', $etag);
    }

    private function getUser($user): User
    {
        $user = Cache::remember("user-{$user}", 3600, function () use ($user) {
            return User::where('id', $user)->first();
        });

        if (! $user) {
            response()->json(['error' => 'User not found'], 404)->throwResponse();
        }

        return $user;
    }
}
