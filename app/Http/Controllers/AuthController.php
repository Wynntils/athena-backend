<?php

namespace App\Http\Controllers;

use App\Http\Libraries\MinecraftFakeAuth;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function getPublicKey(): array
    {
        return ['publicKeyIn' => MinecraftFakeAuth::instance()->getPublicKey()];
    }

    public function responseEncryption(Request $request): array
    {
        $body = $request->json();

        $profile = MinecraftFakeAuth::instance()->getGameProfile($body->get('username'), $body->get('key'));

        return [];
    }
}
