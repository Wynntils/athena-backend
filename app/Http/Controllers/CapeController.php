<?php

namespace App\Http\Controllers;

use App\Http\Libraries\CapeManager;
use App\Http\Requests\CapeRequest;
use App\Models\User;
use Illuminate\Http\Request;

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

        [$width, $height] = getimagesize($capePath);

        if ($width % 64 !== 0 || $height % ($width / 2) !== 0) {
            return response()->json(['message' => 'The image needs to be multiple of 64x32.'], 400);
        }

//        TODO: $this->manager->maskCape($capePath);

        $hash = sha1_file($capePath);

        if ($this->manager->isApproved($hash)) {
            return response()->json(['message' => 'The provided cape is already approved.']);
        }

        if ($this->manager->isQueued($hash)) {
            return response()->json(['message' => 'The provided cape is already queued.']);
        }

        if ($this->manager->isBanned($hash)) {
            return response()->json(['message' => 'The provided cape is banned.']);
        }

        $this->manager->queueCape($request->file('cape'));

        return response()->json([
            'message' => 'Added to queue', 'sha-1' => $hash, 'name' => $request->file('cape')?->getFilename()
        ]);
    }

    public function approveCape(Request $request): \Illuminate\Http\JsonResponse
    {
        $sha = $request->route('sha');
        if (!$this->manager->isQueued($sha))
        {
            return response()->json(['message' => 'There\'s not a cape in the queue with the provided SHA-1'], 400);
        }

        $this->manager->approveCape($sha);
        return response()->json(['message' => 'Successfully approved the cape.']);
    }

    public function banCape(Request $request): \Illuminate\Http\JsonResponse
    {
        $sha1 = $request->route('sha');

        $this->manager->banCape($sha1);

        return response()->json(['message' => 'The provided cape was banned successfully']);
    }
}
