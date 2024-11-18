<?php

namespace App\Http\Controllers;

use App\Enums\MaskType;
use App\Http\Requests\CapeRequest;
use App\Managers\CapeManager;
use App\Models\User;
use Illuminate\Http\Request;
use Image;

class CapeController extends Controller
{
    protected CapeManager $manager;

    public function __construct(CapeManager $capeManager)
    {
        $this->manager = $capeManager;
    }

    public function getCape($capeId)
    {
        return response()->file($this->manager->getCape($capeId), [
            'Content-Type' => 'image/png',
        ]);
    }

    public function getUserCape($uuid)
    {
        return response()->file($this->manager->getCape(User::findOrFail($uuid)->cosmeticInfo?->getFormattedTexture() ?? ''), [
            'Content-Type' => 'image/png',
        ]);
    }

    public function list(): \Illuminate\Http\JsonResponse
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

    public function queueGetCape($capeId)
    {
        return response()->file($this->manager->getQueuedCape($capeId), [
            'Content-Type' => 'image/png',
        ]);
    }

    public function queueList(): \Illuminate\Http\JsonResponse
    {
        return response()->json(['result' => $this->manager->listQueuedCapes()]);
    }

    public function uploadCape(CapeRequest $request): \Illuminate\Http\JsonResponse
    {
        $capePath = $request->validated('cape')?->path();

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
            default => response()->json(['message' => 'The cape has been queued for approval.', 'sha-1' => $this->manager->queueCape($image)]),
        };
    }

    public function approveCape(Request $request): \Illuminate\Http\JsonResponse
    {
        $sha = $request->route('sha');
        $type = MaskType::tryFrom($request->route('type')) ?? MaskType::FULL;

        if (!$this->manager->isQueued($sha)) {
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
                foreach (User::where('cosmeticInfo.capeTexture', $originalSha)->get() as $user) {
                    $cosmeticInfo = $user->cosmeticInfo;
                    $cosmeticInfo->capeTexture = $sha;
                    $cosmeticInfo->save();
                }

            }
        }

        $this->manager->approveCape($sha);
        return response()->json(['message' => 'Successfully approved the cape.']);
    }

    public function banCape(Request $request): \Illuminate\Http\JsonResponse
    {
        $sha1 = $request->route('sha');

        if (!$this->manager->isQueued($sha1) && !$this->manager->isApproved($sha1))
        {
            return response()->json(['message' => 'There\'s not a cape with the provided SHA-1'], 404);
        }

        $this->manager->banCape($sha1);

        return response()->json(['message' => 'The provided cape was banned successfully']);
    }
}
