<?php

namespace App\Http\Controllers;

use App\Http\Libraries\CapeManager;
use App\Http\Requests\CapeRequest;
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
        return response()->file($this->manager->getCape($capeId));
    }

    public function getUserCape($uuid)
    {
        return response()->file($this->manager->getCape(User::findOrFail($uuid)->cosmeticInfo->getFormattedTexture()));
    }

    public function list(): \Illuminate\Http\JsonResponse
    {
        return response()->json(['result' => $this->manager->listCapes()]);
    }

    public function queueGetCape($capeId)
    {
        return response()->file($this->manager->getQueuedCape($capeId));
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

        $image->encode('png');

        $hash = sha1($image->getEncoded());

        return match (true) {
            $this->manager->isApproved($hash) => response()->json(['message' => 'The provided cape is already approved.'], 400),
            $this->manager->isQueued($hash) => response()->json(['message' => 'The provided cape is already queued.'], 400),
            $this->manager->isBanned($hash) => response()->json(['message' => 'The provided cape is banned.'], 400),
            default => response()->json(['message' => 'The cape has been queued for approval.', 'sha-1' => $this->manager->queueCape($image)]),
        };
    }

    public function approveCape(Request $request): \Illuminate\Http\JsonResponse
    {
        $sha = $request->route('sha');
        if (!$this->manager->isQueued($sha))
        {
            return response()->json(['message' => 'There\'s not a cape in the queue with the provided SHA-1'], 404);
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
