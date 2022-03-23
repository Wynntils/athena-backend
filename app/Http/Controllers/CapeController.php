<?php

namespace App\Http\Controllers;

use App\Http\Libraries\CapeManager;
use App\Models\User;
use Illuminate\Http\Request;
use Validator;

class CapeController extends Controller
{
    protected CapeManager $manager;

    public function __construct(CapeManager $capeManager)
    {
        $this->manager = $capeManager;
    }

    public function getCape($capeId)
    {
        return response($this->manager->getCape($capeId))->header('Content-Type', 'image/png');
    }

    public function getUserCape($uuid)
    {
        return response($this->manager->getCape(User::findOrFail($uuid)->cosmeticInfo->getFormattedTexture()),
            200)->header('Content-Type', 'image/png');
    }

    public function list(): \Illuminate\Http\JsonResponse
    {
        return response()->json(['result' => $this->manager->listCapes()]);
    }

    public function delete(Request $request, $token)
    {
        $this->checkToken($token);

        $sha1 = $request->json('sha-1');

        if(!$this->manager->deleteCape($sha1)) {
            return response()->json(['message' => 'The provided cape SHA-1 doesn\'t exists']);
        }

        return response()->json(['message' => 'The provided cape was deleted successfully']);
    }

    public function queueGetCape($capeId)
    {
        if ($this->manager->isApproved($capeId)) {
            return $this->getCape($capeId);
        }

        return $this->manager->getQueuedCape($capeId);
    }

    public function queueList()
    {
        return response()->json(['result' => $this->manager->listQueuedCapes()]);
    }

    public function uploadCape(Request $request, $token)
    {
        $this->checkToken($token);

        $validator = Validator::make($request->all(), [
            'cape' => 'required|file|mimes:png|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()], 403);
        }

        [$width, $height] = getimagesize($request->file('cape')?->path());

        if ($width % 64 !== 0 || $height % ($width / 2) !== 0) {
            return response()->json(['message' => 'The image needs to be multiple of 64x32.'], 400);
        }

//        TODO: $this->manager->maskCape();

        $hash = sha1_file($request->file('cape')?->path());

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

    public function approveCape($token, $sha)
    {
        $this->checkToken($token);

        if (!$this->manager->isQueued($sha))
        {
            return response()->json(['message' => 'There\'s not a cape in the queue with the provided SHA-1'], 400);
        }

        $this->manager->approveCape($sha);
        return response()->json(['message' => 'Successfully approved the cape.']);
    }

    public function banCape($token, $sha)
    {
        $this->checkToken($token);

        if (!$this->manager->isQueued($sha))
        {
            return response()->json(['message' => 'There\'s not a cape in the queue with the provided SHA-1'], 400);
        }

        $this->manager->banCape($sha);
        return response()->json(['message' => 'Successfully banned the cape.']);
    }

    private function checkToken($token)
    {
        if ($token !== 'test') {
            abort(401);
        }
    }
}
