<?php

namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Support\Facades\Storage;
use Jenssegers\Mongodb\Eloquent\Model;

/**
 * @property string $username
 * @property string $authToken
 * @property string $accountType
 * @property DiscordInfo $discordInfo
 * @property CosmeticInfo $cosmeticInfo
 *
 * @mixin Builder
 */
class User extends Model implements
    AuthenticatableContract,
    AuthorizableContract
{
    use Authenticatable, Authorizable;

    public $timestamps = false;
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'username',
        'password',
    ];
    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'authToken',
        'remember_token',
    ];

    public function discordInfo(): \Jenssegers\Mongodb\Relations\EmbedsOne
    {
        return $this->embedsOne(DiscordInfo::class);
    }

    public function cosmeticInfo(): \Jenssegers\Mongodb\Relations\EmbedsOne
    {
        return $this->embedsOne(CosmeticInfo::class);
    }

    public function updateAccount($username, $version): string
    {
        $this->authToken = \Str::uuid()->toString();
        $this->lastActivity = currentTimeMillis();
        $this->username = $username;

        $this->latestVersion = $version;

        $usedVersions = $this->usedVersions;
        $usedVersions[$version] = currentTimeMillis();
        $this->usedVersions = $usedVersions;

        $this->save();

        return $this->authToken;
    }

    public function updateDiscord($id, $username): void
    {
        $this->discordInfo = [
            'username' => $username,
            'id' => $id
        ];

        $this->save();
    }

    public function getConfigFiles(): \ArrayObject
    {
        $files = new \ArrayObject();
        $configs = Storage::disk('configs');
        foreach ($configs->files($this->id) as $file) {
            $files[basename($file)] = json_encode(json_decode(zlib_decode($configs->get($file))));
        }

        return $files;
    }

    public function getConfigAmount(): int
    {
        return count(Storage::disk('configs')->files($this->id));
    }

    public function setConfig($configName, $content): void
    {
        $configs = Storage::disk('configs');
        $configs->put($this->id.'/'.$configName, $content);
    }
}
