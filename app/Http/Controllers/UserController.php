<?php

namespace App\Http\Controllers;

use App\Enums\AccountType;
use App\Events\CapeSubmittedEvent;
use App\Http\Libraries\CapeManager;
use App\Http\Requests\UserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\CosmeticAssetService;
use Auth;
use Dedoc\Scramble\Attributes\ExcludeRouteFromDocs;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image as ImageFactory;

#[Group('User')]
class UserController extends Controller
{
    public function __construct(
        private CapeManager $capeManager,
        private CosmeticAssetService $cosmeticService,
    ) {}

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
                'accountType' => $user->account_type,
                'cosmetics' => [
                    'hasCape' => $user->hasCape(),
                    'hasElytra' => $user->hasElytra(),
                    'hasEars' => $user->hasPart('ears'),
                    'texture' => $this->capeManager->getCapeAsBase64($user->getFormattedTexture(), true),
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
            'accountType' => $user->account_type,
        ];

        // Conditionally add cosmetics info
        if ($cosmetics) {

            $texture = $this->capeManager->getCapeAsBase64($user->getFormattedTexture(), true);
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

        $etag = '"'.md5($user->account_type->value.json_encode($user->cosmetic_info)).'"';

        if ($request->header('If-None-Match') === $etag) {
            return response('', 304);
        }

        return (new UserResource($user))->response()->header('ETag', $etag);
    }

    /**
     * Upload a cape for the authenticated user
     */
    public function uploadCapeWeb(UserRequest $request): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = \Auth::user();

        if ($user->account_type === AccountType::BANNED) {
            return response()->json(['message' => 'Your account has been banned.'], 403);
        }

        $capePath = $request->validated('cape')?->path();
        $image = ImageFactory::make($capePath);

        $width = $image->width();
        $height = $image->height();

        if ($width % 64 !== 0 || $height % ($width / 2) !== 0) {
            return response()->json(['message' => 'Image dimensions must be a multiple of 64×32.'], 400);
        }

        $animated = $height > ($width / 2);

        // Determine tier limits
        $tier = $user->account_type;
        $isUnlimited = in_array($tier, [AccountType::MODERATOR], true);
        $isDonatorTier = in_array($tier, [AccountType::DONATOR, AccountType::CONTENT_TEAM], true);
        $isNormalTier = in_array($tier, [AccountType::NORMAL, AccountType::HELPER], true);

        // Check animated permission first
        $canAnimate = $isDonatorTier || $isUnlimited;
        if ($animated && !$canAnimate) {
            return response()->json(['message' => 'Animated capes require a Donator account or higher.'], 400);
        }

        // Check resolution limits
        if (!$isUnlimited) {
            if ($isNormalTier && ($width > 64 || $height > 32)) {
                return response()->json(['message' => 'Resolution exceeds your account tier.'], 400);
            }

            if ($isDonatorTier && ($width > 256 || $height > 128)) {
                return response()->json(['message' => 'Resolution exceeds your account tier.'], 400);
            }
        }

        $this->capeManager->maskCapeImage($image);

        $sha = $this->capeManager->getSha($image);

        if ($this->capeManager->isApproved($sha)) {
            return response()->json([
                'message'  => 'The provided cape is already approved.',
                'sha-1'    => $sha,
                'animated' => $animated,
            ]);
        }

        if ($this->capeManager->isQueued($sha)) {
            return response()->json(['message' => 'The provided cape is already queued.', 'sha-1' => $sha], 400);
        }

        if ($this->capeManager->isBanned($sha)) {
            return response()->json(['message' => 'The provided cape is banned.', 'sha-1' => $sha], 400);
        }

        $metadata = [
            'name'       => $request->validated('name'),
            'visibility' => $request->validated('visibility') ?? 'public',
            'tags'       => $request->validated('tags') ?? [],
        ];

        $sha = $this->capeManager->queueCape($image, $user->username, true, $user, $metadata);
        CapeSubmittedEvent::dispatch($user->username);
        Cache::forget('capes.list');

        return response()->json([
            'message'  => 'The cape has been queued for approval.',
            'sha-1'    => $sha,
            'animated' => $animated,
        ]);
    }

    /**
     * Set or clear the active cape for the authenticated user
     */
    public function selectCape(UserRequest $request): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if ($user->account_type === AccountType::BANNED) {
            return response()->json(['message' => 'Your account has been banned.'], 403);
        }

        $sha  = $request->validated('sha');

        if ($sha === '' || $sha === null) {
            $oldSha                      = $user->cosmetic_info['capeTexture'] ?? null;
            $cosmeticInfo                = $user->cosmetic_info ?? [];
            $cosmeticInfo['capeTexture'] = '';
            $user->cosmetic_info         = $cosmeticInfo;
            $user->save();
            if ($oldSha) $this->cosmeticService->decrementEquipCount($oldSha);

            return response()->json(['message' => 'Cape cleared.']);
        }

        if (! $this->capeManager->isApproved($sha)) {
            return response()->json(['message' => 'That cape is no longer available.'], 404);
        }

        // Check animated flag from cache, fall back to file read
        $capeList = Cache::get('capes.list', []);
        $capeMeta = collect($capeList)->firstWhere('sha', $sha);

        if ($capeMeta !== null) {
            $animated = $capeMeta['animated'];
        } else {
            try {
                $path     = Storage::disk('approved')->path($sha);
                $image    = ImageFactory::make($path);
                $animated = $image->height() > ($image->width() / 2);
            } catch (\Throwable) {
                $animated = false;
            }
        }

        $canAnimate = in_array($user->account_type, [
            AccountType::DONATOR,
            AccountType::CONTENT_TEAM,
            AccountType::MODERATOR,
        ], true);

        if ($animated && !$canAnimate) {
            return response()->json(['message' => 'Animated capes require a Donator account or higher.'], 403);
        }

        $oldSha                      = $user->cosmetic_info['capeTexture'] ?? null;
        $cosmeticInfo                = $user->cosmetic_info ?? [];
        $cosmeticInfo['capeTexture'] = $sha;
        $user->cosmetic_info         = $cosmeticInfo;
        $user->save();
        if ($oldSha && $oldSha !== $sha) $this->cosmeticService->decrementEquipCount($oldSha);
        $this->cosmeticService->incrementEquipCount($sha);

        return response()->json(['message' => 'Cape updated.']);
    }

    /**
     * Get elytra mode for the authenticated user
     */
    public function getElytraMode(): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $elytraEnabled = ($user->cosmetic_info['elytraEnabled'] ?? false) === true;

        return response()->json(['elytraEnabled' => $elytraEnabled]);
    }

    /**
     * Set elytra mode for the authenticated user
     */
    public function setElytraMode(UserRequest $request): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if ($user->account_type === AccountType::BANNED) {
            return response()->json(['message' => 'Your account has been banned.'], 403);
        }

        $elytraEnabled               = $request->validated('elytraEnabled');
        $cosmeticInfo                = $user->cosmetic_info ?? [];
        $cosmeticInfo['elytraEnabled'] = $elytraEnabled;
        $user->cosmetic_info         = $cosmeticInfo;
        $user->save();

        return response()->json([
            'message'       => $elytraEnabled ? 'Elytra mode enabled.' : 'Cape mode enabled.',
            'elytraEnabled' => $elytraEnabled,
        ]);
    }

    private function getUser($user): User
    {
        $user = Cache::remember("user-{$user}", 3600, function () use ($user) {
            return User::find($user);
        });

        if (! $user) {
            response()->json(['error' => 'User not found'], 404)->throwResponse();
        }

        return $user;
    }
}
