<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Builder;
use MongoDB\Laravel\Eloquent\Model;

/**
 * @property CosmeticPart $cape
 * @property string $capeStyle
 * @mixin Builder
 */
class CapeSlot extends Model
{
    public $timestamps = false;
    protected $fillable = [
        'cape',
        'style'
    ];
    protected $casts = [
        'cape' => CosmeticPart::class,
        'style' => 'string'
    ];

    public function cape()
    {
        return $this->belongsTo(CosmeticPart::class);
    }
}
