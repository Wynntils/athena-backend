<?php

namespace App\Models;

use App\Enums\CosmeticSlot as CosmeticSlotEnum;
use MongoDB\Laravel\Eloquent\Builder;
use MongoDB\Laravel\Eloquent\Model;

/**
 * @property CosmeticPart $part
 * @property CosmeticSlotEnum $slot
 *
 * @mixin Builder
 */
class CosmeticSlot extends Model
{
    public $timestamps = false;
    protected $fillable = [
        'slot',
        'part'
    ];

    protected $casts = [
        'slot' => CosmeticSlotEnum::class
    ];

    public function part()
    {
        return $this->belongsTo(CosmeticPart::class);
    }
}
