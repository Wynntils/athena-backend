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
        return response($this->manager->getCape($capeId), 200)->header('Content-Type', 'image/png');
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
            return response()->json(['message' => $validator->errors()], 401);
        }

        [$width, $height] = getimagesize($request->file('cape')?->path());

        if($width % 64 !== 0 || $height % ($width / 2) !== 0) {
            return response()->json(['message' => 'The image needs to be multiple of 64x32.'], 401);
        }

//        $this->manager->maskCape();

        $hash = sha1_file($request->file('cape')?->path());

        if($this->manager->isApproved($hash)) {

        }

        if ($this->manager->isQueued($hash)) {

        }

        if ($this->manager->isBanned($hash)) {

        }

        

        // $request->file('cape')->storeAs('')

    }

    public function delete($token)
    {
        $this->checkToken($token);
    }

    public function approveCape($token, $sha)
    {
        $this->checkToken($token);
    }

    public function banCape($token, $sha)
    {
        $this->checkToken($token);
    }

    private function checkToken($token)
    {
        if ($token !== 'test') {
            abort(401);
        }
    }
}
