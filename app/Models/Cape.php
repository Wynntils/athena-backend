<?php

namespace App\Models;

use App\Http\Enums\CapeType;
use Illuminate\Database\Eloquent\Builder;
use Jenssegers\Mongodb\Eloquent\Model;

/**
 * Class Cape
 *
 * @property string $_id
 * @property string $uploadedBy
 * @property CapeType $type
 * @property string $created_at
 *
 * @mixin Builder
 */
class Cape extends Model
{
    protected $fillable = [
        '_id',
        'uploadedBy',
        'type',
    ];

    protected $casts = [
        'type' => CapeType::class,
    ];
}
