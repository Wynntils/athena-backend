<?php

namespace App\Http\Controllers;

use App\Http\Libraries\CapeManager;
use App\Models\User;

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

    public function list()
    {
        return response()->json(['result' => $this->manager->listCapes()]);
    }

    public function delete($token)
    {

    }

    public function getAnalyseCape($id)
    {

    }

    public function queueList()
    {

    }

    public function uploadCape($token)
    {

    }

    public function approveCape($token, $sha)
    {

    }

    public function banCape($token, $sha)
    {

    }
}
