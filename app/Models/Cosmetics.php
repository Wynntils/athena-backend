<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

/**
 * @property CosmeticPart $skin
 * @property CapeSlot $cape
 * @property array<CosmeticSlot> $slots
 */
class Cosmetics extends Model
{
    public $timestamps = false;

    protected $with = ['slots', 'skin', 'cape'];

    protected $fillable = [
        'skin',
        'cape',
        'slots',
    ];

    protected $casts = [
        'skin' => CosmeticPart::class,
        'cape' => CapeSlot::class,
        'slots' => CosmeticSlot::class . '[]',
    ];

    public function slots()
    {
        return $this->embedsMany(CosmeticSlot::class);
    }

    public function skin()
    {
        return $this->belongsTo(CosmeticPart::class);
    }

    public function cape()
    {
        return $this->embedsOne(CapeSlot::class);
    }
}
