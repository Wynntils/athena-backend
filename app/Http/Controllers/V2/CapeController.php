<?php

namespace App\Http\Controllers\V2;

use App\Http\Enums\CapeType;
use App\Http\Requests\CapeRequest;
use App\Http\Resources\CapeResource;
use App\Models\Cape;

class CapeController extends \App\Http\Controllers\CapeController
{
    public function getCapeInfo($capeId): \Illuminate\Http\JsonResponse
    {
        return response()->json(new CapeResource(Cape::find($capeId)));
    }

    public function uploadCape(CapeRequest $request): \Illuminate\Http\JsonResponse
    {
        $response = parent::uploadCape($request);

        if($response->status() !== 200) {
            return $response;
        }

        $user = $request->user();

        $cape = Cape::firstOrCreate([
            '_id' => $response->getData()->{'sha-1'},
        ], [
            'uploadedBy' => $user->_id,
            'type' => CapeType::USER
        ]);

        $user->cosmeticInfo->capeTexture = $cape->_id;
        $user->save();

        return response()->json(new CapeResource($cape), 201);
    }
}
