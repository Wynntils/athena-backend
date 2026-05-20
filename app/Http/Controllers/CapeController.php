<?php

namespace App\Http\Controllers;

use App\Enums\MaskType;
use App\Events\CapeSubmittedEvent;
use App\Http\Libraries\CapeManager;
use App\Http\Requests\CapeRequest;
use App\Models\User;
use App\Services\CosmeticAssetService;
use Dedoc\Scramble\Attributes\ExcludeRouteFromDocs;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Image;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

#[Group('Cape')]
class CapeController extends Controller
{
    public function __construct(
        protected CapeManager $manager,
        protected CosmeticAssetService $cosmeticService,
    ) {}

    #[ExcludeRouteFromDocs]
    public function getCape($capeId): BinaryFileResponse
    {
        return response()->file($this->manager->getCape($capeId), [
            'Content-Type' => 'image/png',
        ]);
    }

    #[ExcludeRouteFromDocs]
    public function getUserCape($uuid): BinaryFileResponse
    {
        $user = User::findOrFail($uuid);

        return response()->file($this->manager->getCape($user->getFormattedTexture()), [
            'Content-Type' => 'image/png',
        ]);
    }

    #[ExcludeRouteFromDocs]
    public function list(Request $request): JsonResponse|Response
    {
        $page = max(1, (int) $request->query('page', 1));
        $perPage = min(100, max(1, (int) $request->query('per_page', 50)));

        $all = $this->manager->listCapes();
        $total = count($all);

        $data = array_slice($all, ($page - 1) * $perPage, $perPage);
        $lastPage = max(1, (int) ceil($total / $perPage));

        $etag = md5("{$total}-{$page}-{$perPage}");

        $response = response()->json([
            'data' => array_values($data),
            'total' => $total,
            'page' => min($page, $lastPage),
            'per_page' => $perPage,
            'last_page' => $lastPage,
        ])->setEtag($etag)
            ->setCache(['max_age' => 60, 's_maxage' => 60, 'public' => true])
            ->setExpires(now()->addSeconds(60));

        if ($response->isNotModified($request)) {
            return $response;
        }

        return $response;
    }

    #[ExcludeRouteFromDocs]
    public function queueGetCape($capeId): BinaryFileResponse
    {
        return response()->file($this->manager->getQueuedCape($capeId), [
            'Content-Type' => 'image/png',
        ]);
    }

    #[ExcludeRouteFromDocs]
    public function queueList(): JsonResponse
    {
        return response()->json(['result' => $this->manager->listQueuedCapes()]);
    }

    #[ExcludeRouteFromDocs]
    public function uploadCape(CapeRequest $request): JsonResponse
    {
        $capePath = $request->validated('cape')?->path();
        $username = $request->validated('username');

        $image = Image::make($capePath);

        if ($image->width() % 64 !== 0 || $image->height() % ($image->width() / 2) !== 0) {
            return response()->json(['message' => 'The image needs to be multiple of 64x32.'], 400);
        }

        $this->manager->maskCapeImage($image);

        $hash = $this->manager->getSha($image);

        if ($this->manager->isApproved($hash)) {
            return response()->json(['message' => 'The cape has been queued for approval.', 'sha-1' => $hash]);
        }

        if ($this->manager->isQueued($hash)) {
            return response()->json(['message' => 'The provided cape is already queued.', 'sha-1' => $hash], 400);
        }

        if ($this->manager->isBanned($hash)) {
            return response()->json(['message' => 'The provided cape is banned.', 'sha-1' => $hash], 400);
        }

        $sha = $this->manager->queueCape($image, $username);
        CapeSubmittedEvent::dispatch($username);

        return response()->json(['message' => 'The cape has been queued for approval.', 'sha-1' => $sha]);
    }

    #[ExcludeRouteFromDocs]
    public function approveCape(Request $request): JsonResponse
    {
        $sha = $request->route('sha');
        $type = MaskType::tryFrom($request->route('type')) ?? MaskType::FULL;

        if (! $this->manager->isQueued($sha)) {
            return response()->json(['message' => 'There\'s not a cape in the queue with the provided SHA-1'], 404);
        }

        if ($type !== MaskType::FULL) {
            // Copy the sha
            $originalSha = $sha;

            // Mask the image and approve it
            $newImage = Image::make($this->manager->getQueuedCape($sha));
            $this->manager->maskCapeImage($newImage, $type);
            // Check if the image content is the same
            if ($this->manager->getSha($newImage) !== $originalSha) {
                $sha = $this->manager->queueCape($newImage, '');

                // Delete the old image
                $this->manager->deleteQueuedCape($originalSha);

                // Set users who had the cape to have the new cape
                foreach (User::byCapeTexture($originalSha)->get() as $user) {
                    $cosmeticInfo = $user->cosmetic_info;
                    $cosmeticInfo['capeTexture'] = $sha;
                    $user->cosmetic_info = $cosmeticInfo;
                    $user->save();
                }

            }
        }

        $this->manager->approveCape($sha);

        return response()->json(['message' => 'Successfully approved the cape.']);
    }

    #[ExcludeRouteFromDocs]
    public function approveEdit(Request $request): JsonResponse
    {
        $sha = $request->route('sha');
        $asset = \App\Models\CosmeticAsset::bySha($sha)->first();

        if (! $asset) {
            return response()->json(['message' => 'Asset not found.'], 404);
        }

        if (! $asset->hasPendingEdit()) {
            return response()->json(['message' => 'No pending edit exists.'], 404);
        }

        $this->cosmeticService->approveEdit($sha);

        return response()->json(['message' => 'Edit approved.']);
    }

    #[ExcludeRouteFromDocs]
    public function rejectEdit(Request $request): JsonResponse
    {
        $sha = $request->route('sha');
        $asset = \App\Models\CosmeticAsset::bySha($sha)->first();

        if (! $asset) {
            return response()->json(['message' => 'Asset not found.'], 404);
        }

        $this->cosmeticService->rejectEdit($sha);

        return response()->json(['message' => 'Edit rejected.']);
    }

    #[ExcludeRouteFromDocs]
    public function banCape(Request $request): JsonResponse
    {
        $sha1 = $request->route('sha');

        if (! $this->manager->isQueued($sha1) && ! $this->manager->isApproved($sha1)) {
            return response()->json(['message' => 'There\'s not a cape with the provided SHA-1'], 404);
        }

        $this->manager->banCape($sha1);

        return response()->json(['message' => 'The provided cape was banned successfully']);
    }
}
