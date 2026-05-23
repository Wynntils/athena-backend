<?php

namespace App\Http\Controllers;

use App\Http\Requests\WebLoginRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class WebAuthController extends Controller
{
    public function login(WebLoginRequest $request): JsonResponse
    {
        $user = User::where('username', $request->validated('username'))->first();

        if (! $user || ! Hash::check($request->validated('password'), $user->password)) {
            return response()->json(['message' => 'Invalid username or password.'], 401);
        }

        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        $token = $user->createToken($request->header('User-Agent') ?? 'web')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => $this->formatUser($user),
        ]);
    }

    public function me(): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (! $user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        return response()->json([
            'user' => $this->formatUser($user),
        ]);
    }

    public function logout(): JsonResponse
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();

        if ($user?->currentAccessToken()) {
            $user->currentAccessToken()->delete();
        }

        Auth::logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();

        return response()->json(['message' => 'Logged out.']);
    }

    private function formatUser(User $user): array
    {
        $discordInfo = $user->discord_info ?? [];
        $cosmeticInfo = $user->cosmetic_info ?? [];

        return [
            'uuid' => $user->id,
            'username' => $user->username,
            'accountType' => $user->account_type->value,
            'discord' => [
                'id' => $discordInfo['id'] ?? null,
                'username' => $discordInfo['username'] ?? null,
            ],
            'cosmetics' => [
                'capeTexture' => $cosmeticInfo['capeTexture'] ?? null,
                'elytraEnabled' => ($cosmeticInfo['elytraEnabled'] ?? false) === true,
            ],
        ];
    }
}
