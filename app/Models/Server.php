<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Jenssegers\Mongodb\Eloquent\Model;

/**
 * @mixin Builder
 */
class Server extends Model
{

    protected $fillable = [
        '_id',
        'firstSeen'
    ];

    public $timestamps = false;
}
