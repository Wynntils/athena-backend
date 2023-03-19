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
            return redirect()->route('auth.login')->withErrors($request->input('error'));
        }

        try {
            $socialiteUser = Socialite::driver($provider)->user();
        } catch (ClientException $e) {
            if ($e->getCode() === 401) {
                return redirect()->route('auth.login')->withErrors('Invalid credentials');
            }

            $uniqueErrorId = uniqid('', true); // Generate a unique ID for this error to help us debug

            \Log::error("Error while logging in with $provider: $uniqueErrorId", [
                'exception' => $e,
            ]);

            return redirect()->route('auth.login')->withErrors("An error occurred while logging in. Please try again later. Error ID: $uniqueErrorId");
        } catch (\Exception $e) { // This is a catch-all for any other exceptions
            $uniqueErrorId = uniqid('', true); // Generate a unique ID for this error to help us debug

            \Log::error("Error while logging in with $provider: $uniqueErrorId", [
                'exception' => $e,
            ]);

            return redirect()->route('auth.login')->withErrors("An error occurred while logging in. Please try again later. Error ID: $uniqueErrorId");
        }

        switch ($provider) {
            case 'discord':
                $user = User::where('discordInfo.id', $socialiteUser->id)->first();
                if (!$user) {
                    return redirect()->route('auth.login')->withErrors('No Wynntils account is linked to this Discord account.');
                }
                break;
            case 'minecraft':
                $user = User::find($socialiteUser->uuid);
                if (!$user) {
                    return redirect()->route('auth.login')->withErrors('No Wynntils account is linked to this Minecraft account.');
                }
                break;
            default:
                return redirect()->route('auth.login')->withErrors('Invalid provider');
        }

        auth()->login($user, true);

        return redirect()->route('crash.index');
    }

}
