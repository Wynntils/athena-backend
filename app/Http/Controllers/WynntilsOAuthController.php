<?php

namespace App\Http\Controllers;

use App\Models\User;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;

class WynntilsOAuthController
{
    public function redirectToProvider($provider)
    {
        switch ($provider) {
            case 'discord':
                return Socialite::driver($provider)->setScopes(['identify'])->redirect();
            case 'minecraft':
                return Socialite::driver($provider)->redirect();
            default:
                abort(404);
        }
    }

    public function handleProviderCallback(Request $request, $provider)
    {
        if ($request->has('error')) {
            return view('auth.oauth-callback', ['message' => $request->input('error')]);
        }

        try {
            $socialiteUser = Socialite::driver($provider)->user();
        } catch (ClientException $e) {
            if ($e->getCode() === 401) {
                return view('auth.oauth-callback', ['message' => 'Invalid credentials']);
            }

            $uniqueErrorId = uniqid('', true);

            \Log::error("Error while logging in with $provider: $uniqueErrorId", ['exception' => $e]);

            return view('auth.oauth-callback', [
                'message' => "An error occurred while logging in. Please try again later. Error ID: $uniqueErrorId",
            ]);
        } catch (\Exception $e) {
            $uniqueErrorId = uniqid('', true);

            \Log::error("Error while logging in with $provider: $uniqueErrorId", ['exception' => $e]);

            return view('auth.oauth-callback', [
                'message' => "An error occurred while logging in. Please try again later. Error ID: $uniqueErrorId",
            ]);
        }

        switch ($provider) {
            case 'discord':
                $user = User::byDiscordId($socialiteUser->id)->first();
                if (! $user) {
                    return view('auth.oauth-callback', [
                        'message' => 'No Wynntils account is linked to this Discord account.',
                    ]);
                }
                break;
            case 'minecraft':
                $user = User::find($socialiteUser->uuid);
                if (! $user) {
                    return view('auth.oauth-callback', [
                        'message' => 'No Wynntils account is linked to this Minecraft account.',
                    ]);
                }
                break;
            default:
                return view('auth.oauth-callback', ['message' => 'Invalid provider']);
        }

        auth()->login($user, true);

        return view('auth.oauth-callback', ['token' => $user->auth_token]);
    }
}
