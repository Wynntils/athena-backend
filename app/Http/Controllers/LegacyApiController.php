<?php

namespace App\Http\Controllers;

use App\Http\Enums\AccountType;
use App\Http\Requests\LegacyApiRequest;
use App\Http\Resources\UserResource;
use App\Models\Guild;
use App\Models\User;

class LegacyApiController extends Controller
{
    public function getUserData(LegacyApiRequest $request)
    {
        $user = $this->getUser($request->validated('user'));
        return ["result" => collect(new UserResource($user)), 'message' => 'Successfully found user account.'];
    }

    public function setAccountType(LegacyApiRequest $request)
    {
        $user = $this->getUser($request->validated('user'));
        $user->accountType = AccountType::tryFrom($request->validated('type')) ?? AccountType::NORMAL;
        $user->save();
        return ["result" => collect(new UserResource($user)), 'message' => 'Successfully updated user account.'];
    }

    public function updateCosmetics(LegacyApiRequest $request)
    {
        $user = $this->getUser($request->validated('user'));

        $cosmetics = collect($request->validated('cosmetics'));

        if ($cosmetics->has('texture')) {
            $user->cosmeticInfo->capeTexture = $cosmetics->get('texture');
        }
        if ($cosmetics->has('isElytra')) {
            $user->cosmeticInfo->elytraEnabled = $cosmetics->get('isElytra');
        }
        if ($cosmetics->has('maxResolution')) {
            $user->cosmeticInfo->maxResolution = $cosmetics->get('maxResolution');
        }
        if ($cosmetics->has('allowAnimated')) {
            $user->cosmeticInfo->allowAnimated = $cosmetics->get('allowAnimated');
        }
        if ($cosmetics->has('parts')) {
            foreach ($cosmetics->get('parts') as $part => $value) {
                $user->cosmeticInfo->parts[$part] = $value;
            }
            $user->cosmeticInfo->parts = $cosmetics->get('parts');
        }

        $user->save();
        return ["result" => collect(new UserResource($user)), 'message' => 'Updated users cosmetics successfully.'];
    }

    public function setGuildColor(LegacyApiRequest $request)
    {
        $guild = Guild::findOrFail($request->validated('guild'));
        $guild->color = $request->validated('color');
        $guild->save();
        return ['message' => 'Successfully updated guild.'];
    }

    public function setUserPassword(LegacyApiRequest $request)
    {
        $user = User::where('authToken', $request->validated('token'))->firstOrFail();
        $user->password = \Hash::make($request->validated('password'));
        $user->save();
        return ["result" => collect(new UserResource($user)), 'message' => 'Successfully set user account password.'];
    }

    public function getUserByPassword(LegacyApiRequest $request)
    {
        $user = $this->getUser($request->validated('user'));
        if(\Hash::check($request->validated('password'), $user->password)) {
            return ["result" => collect(new UserResource($user)), 'message' => 'Successfully found and validated user account.'];
        }

        return ['message' => 'Invalid password.'];
    }

    public function getUserConfig(LegacyApiRequest $request)
    {
        $user = $this->getUser($request->validated('user'));
        $lookup = $request->validated('configName');
        if ($lookup === 'list') {
            return ['result' => $user->getConfigFiles()];
        }

        return ['result' => $user->getConfig($lookup)];
    }


    private function getUser($user): User
    {
        return match (true) {
            str($user)->startsWith("uuid-") => User::findOrFail(substr($user, 5)),
            str($user)->match("/[a-zA-Z0-9_]{1,16}/")->isNotEmpty() => User::where('username', $user)->firstOrFail(),
            default => User::where('authToken', $user)->firstOrFail()
        };
    }
}
