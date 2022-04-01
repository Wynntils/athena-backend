<?php

namespace App\Models;

use Jenssegers\Mongodb\Eloquent\Model;

/**
 * @property $id
 * @property $username
 */
class DiscordInfo extends Model
{
    protected $fillable = ['id', 'username'];
}
