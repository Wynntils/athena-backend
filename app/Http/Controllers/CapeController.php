<?php

namespace App\Http\Controllers;

use App\Enums\MaskType;
use App\Http\Libraries\CapeManager;
use App\Http\Requests\CapeRequest;
use App\Models\User;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Image;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

#[Group('Cape')]
class CapeController extends Controller
{
    protected CapeManager $manager;

    public function __construct(CapeManager $capeManager)
    {
        $this->manager = $capeManager;
    }

    /** @deprecated */
    public function getCape($capeId): BinaryFileResponse
    {
        return response()->file($this->manager->getCape($capeId), [
            'Content-Type' => 'image/png',
        ]);
    }

    /** @deprecated */
    public function getUserCape($uuid): BinaryFileResponse
    {
        $user = User::findOrFail($uuid);

        return response()->file($this->manager->getCape($user->getFormattedTexture()), [
            'Content-Type' => 'image/png',
        ]);
    }

    /** @deprecated */
    public function list(): JsonResponse
    {
        $result = $this->manager->listCapes();

        return response()->json(['result' => $result])
            ->setCache([
                'max_age' => 60,
                's_maxage' => 60,
                'public' => true,
            ])
            ->setExpires(now()->addSeconds(60))
            ->setEtag(md5(serialize($result)));
    }

    /** @deprecated */
    public function queueGetCape($capeId): BinaryFileResponse
    {
        return response()->file($this->manager->getQueuedCape($capeId), [
            'Content-Type' => 'image/png',
        ]);
    }

    /** @deprecated */
    public function queueList(): JsonResponse
    {
        return response()->json(['result' => $this->manager->listQueuedCapes()]);
    }

    /** @deprecated */
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

        return match (true) {
            $this->manager->isApproved($hash) => response()->json(['message' => 'The provided cape is already approved.', 'sha-1' => $hash], 400),
            $this->manager->isQueued($hash) => response()->json(['message' => 'The provided cape is already queued.', 'sha-1' => $hash], 400),
            $this->manager->isBanned($hash) => response()->json(['message' => 'The provided cape is banned.', 'sha-1' => $hash], 400),
            default => response()->json(['message' => 'The cape has been queued for approval.', 'sha-1' => $this->manager->queueCape($image, $username)]),
        };
    }

    /** @deprecated */
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
                $sha = $this->manager->queueCape($newImage, false);

                // Delete the old image
                $this->manager->deleteQueuedCape($originalSha);

                // Set users who had the cape to have the new cape
                foreach (User::whereRaw("cosmetic_info->>'capeTexture' = ?", [$originalSha])->get() as $user) {
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

    /** @deprecated */
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
