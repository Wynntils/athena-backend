<?php

namespace App\Models\Embedded;

use Jenssegers\Mongodb\Eloquent\Model;

/**
 * @property $id
 * @property $username
 */
class DiscordInfo extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'id',
        'username',
    ];
}
