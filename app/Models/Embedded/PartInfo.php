<?php

namespace App\Models\Embedded;

use Jenssegers\Mongodb\Eloquent\Model;

/**
 * @property bool|null $ears
 */
class PartInfo extends Model
{
    /**
     * @var array<int, string>
     */
    protected $casts = [
        'ears' => 'bool'
    ];
}
