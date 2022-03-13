<?php

namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Jenssegers\Mongodb\Eloquent\Model;

/**
 * @property string $username
 * @property string $authToken
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

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'username',
        'password',
    ];

    public $timestamps = false;

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

    public function getConfigFiles() {
        // TODO - list all current config files
        return new \ArrayObject();
    }
}
