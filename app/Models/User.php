<?php

namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Jenssegers\Mongodb\Eloquent\Model;

/**
 * @property DiscordInfo $discordInfo
 * @property CosmeticInfo $cosmeticInfo
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
}
