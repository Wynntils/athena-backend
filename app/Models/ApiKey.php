<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use \Jenssegers\Mongodb\Eloquent\Model;

/**
 * @property string $name
 * @property string $description
 * @property string[] $adminContact
 * @property int maxLimit
 * @property array $dailyRequests
 *
 * @mixin Builder
 */
class ApiKey extends Model
{

    protected $collection = 'apiKeys';

    public $timestamps = false;

    protected $fillable = [
        '_id',
        'name',
        'description',
        'adminContact',
        'maxLimit',
        'dailyRequests',
    ];

}
