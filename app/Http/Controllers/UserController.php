<?php

namespace App\Http\Controllers;

use App\Http\Libraries\CapeManager;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function getInfo(Request $request)
    {
        $body = $request->json();

        if (!$body->has('uuid')) {
            return response()->json(['message' => "Expecting parameters 'uuid'."], 400);
        }

        $user = User::find($body->get('uuid'));
        return [
            'user' => [
                'accountType' => $user->accountType,
                'cosmetics' => [
                    'hasCape' => $user->cosmeticInfo->hasCape(),
                    'hasElytra' => $user->cosmeticInfo->hasElytra(),
                    'hasEars' => $user->cosmeticInfo->hasPart("ears"),
                    'texture' => CapeManager::getCapeAsBase64($user->cosmeticInfo->getFormattedTexture())
                ]
            ]
        ];
    }
}
