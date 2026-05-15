<?php

namespace App\Http\Controllers;

use App\Http\Requests\WebLoginRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;

class WebAuthController extends Controller
{
    public function login(WebLoginRequest $request): JsonResponse
    {
        $user = User::where('username', $request->validated('username'))->first();

        if (! $user || ! Hash::check($request->validated('password'), $user->password)) {
            return response()->json(['message' => 'Invalid username or password.'], 401);
        }

        $discordInfo = $user->discord_info ?? [];

        return response()->json([
            'token' => $user->auth_token,
            'user' => [
                'uuid' => $user->id,
                'username' => $user->username,
                'accountType' => $user->account_type->value,
                'discord' => [
                    'id' => $discordInfo['id'] ?? null,
                    'username' => $discordInfo['username'] ?? null,
                ],
            ],
        ]);
    }
}
