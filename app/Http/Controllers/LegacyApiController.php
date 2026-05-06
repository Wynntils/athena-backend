<?php

namespace App\Http\Controllers;

use App\Enums\AccountType;
use App\Http\Requests\LegacyApiRequest;
use App\Models\Guild;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class LegacyApiController extends Controller
{
    /** @deprecated */
    public function getUserData(LegacyApiRequest $request)
    {
        $user = $this->getUser($request->validated('user'));

        return ['result' => $this->toLegacyArray($user), 'message' => 'Successfully found user account.'];
    }

    /** @deprecated */
    public function getLinkedUsersData(LegacyApiRequest $request)
    {
        $userList = $this->getLinkedDiscordUsers($request->validated('user'))->map(function ($user) {
            return $this->toLegacyArray($user);
        });

        return ['result' => $userList, 'message' => 'Successfully found user accounts.'];
    }

    /** @deprecated */
    public function setAccountType(LegacyApiRequest $request)
    {
        $user = $this->getUser($request->validated('user'));
        $user->account_type = AccountType::tryFrom($request->validated('type')) ?? AccountType::NORMAL;
        $user->save();

        return ['result' => $this->toLegacyArray($user), 'message' => 'Successfully updated user account.'];
    }

    /** @deprecated */
    public function updateCosmetics(LegacyApiRequest $request)
    {
        $user = $this->getUser($request->validated('user'));

        $cosmetics = collect($request->validated('cosmetics'));

        $user->cosmetic_info = [
            'capeTexture' => $cosmetics->get('texture') ?? '',
            'elytraEnabled' => $cosmetics->get('isElytra') ?? false,
            'maxResolution' => $cosmetics->get('maxResolution') ?? '128x128',
            'allowAnimated' => $cosmetics->get('allowAnimated') ?? false,
            'parts' => $cosmetics->get('parts') ?? [],
        ];
        $user->save();

        return ['result' => $this->toLegacyArray($user), 'message' => 'Updated users cosmetics successfully.'];
    }

    /** @deprecated */
    public function setGuildColor(LegacyApiRequest $request)
    {
        $guild = Guild::findOrFail($request->validated('guild'));
        $guild->color = $request->validated('color');
        $guild->save();

        return ['message' => 'Successfully updated guild.'];
    }

    /** @deprecated */
    public function setUserPassword(LegacyApiRequest $request)
    {
        $user = User::where('auth_token', $request->validated('token'))->firstOrFail();
        $user->password = \Hash::make($request->validated('password'));
        $user->save();

        return ['result' => $this->toLegacyArray($user), 'message' => 'Successfully set user account password.'];
    }

    /** @deprecated */
    public function getUserByPassword(LegacyApiRequest $request)
    {
        $user = $this->getUser($request->validated('user'));
        if (\Hash::check($request->validated('password'), $user->password)) {
            return ['result' => $this->toLegacyArray($user), 'message' => 'Successfully found and validated user account.'];
        }

        return ['message' => 'Invalid password.'];
    }

    /** @deprecated */
    public function getUserConfig(LegacyApiRequest $request)
    {
        $user = $this->getUser($request->validated('user'));
        $lookup = $request->validated('configName');
        if ($lookup === 'list') {
            return ['result' => $user->getConfigFiles()];
        }

        $config = $user->getConfig($lookup);

        if ($config === null) {
            return ['message' => 'Config not found.'];
        }

        try {
            $result = [];
            $result['message'] = "Successfully located user '$lookup' configuration.";
            $result['result'] = json_decode($config, true, 512, JSON_THROW_ON_ERROR);

            return $result;
        } catch (\JsonException $e) {
            return ['message' => "Failed to parse user '$lookup' configuration.", $config];
        }
    }

    private function toLegacyArray(User $user): array
    {
        $discordInfo = $user->discord_info ?? [];
        $cosmeticInfo = $user->cosmetic_info ?? [];

        return [
            'uuid'        => $user->id,
            'username'    => $user->username,
            'accountType' => $user->account_type->value,
            'authToken'   => $user->auth_token,
            'versions'    => [
                'latest' => $user->latest_version,
                'used'   => $user->used_versions ?? [],
            ],
            'discord'     => [
                'username' => $discordInfo['username'] ?? null,
                'id'       => $discordInfo['id'] ?? null,
            ],
            'cosmetics'   => [
                'texture'       => $cosmeticInfo['capeTexture'] ?? '',
                'isElytra'      => $cosmeticInfo['elytraEnabled'] ?? false,
                'maxResolution' => $cosmeticInfo['maxResolution'] ?? '0x0',
                'allowAnimated' => $cosmeticInfo['allowAnimated'] ?? false,
                'parts'         => $cosmeticInfo['parts'] ?? [],
            ],
        ];
    }

    /**
     * @throws ModelNotFoundException
     */
    private function getUser($user): User
    {
        return match (true) {
            str($user)->startsWith('uuid-') => User::findOrFail(substr($user, strlen('uuid-'))),
            str($user)->startsWith('<@') => User::whereRaw("discord_info->>'id' = ?", [str_replace(['<@!', '<@', '>'], '', $user)])->firstOrFail(),
            str($user)->match('/[a-zA-Z0-9_]{1,16}/')->isNotEmpty() => User::where('username',
                $user)->firstOrFail(),
            default => User::where('auth_token', $user)->firstOrFail()
        };
    }

    private function getLinkedDiscordUsers($discordId): \Illuminate\Support\Collection
    {
        return User::whereRaw("discord_info->>'id' = ?", [str_replace(['<@!', '<@', '>'], '', $discordId)])->get();
    }
}
