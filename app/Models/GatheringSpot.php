<?php

namespace App\Models;

use App\Http\Enums\GatheringMaterial;
use App\Http\Enums\ProfessionType;
use Illuminate\Database\Eloquent\Builder;
use Jenssegers\Mongodb\Eloquent\Model;

/**
 * @property ProfessionType $type
 * @property GatheringMaterial $material
 * @property string $lastSeen
 * @property array $users
 *
 * @mixin Builder
 */
class GatheringSpot extends Model
{
    protected $collection = 'gatheringSpot';

    public $timestamps = false;

    protected $fillable = [
        '_id',
        'type',
        'material',
        'lastSeen',
        'users',
    ];

    protected $casts = [
        'type' => ProfessionType::class,
        'material' => GatheringMaterial::class,
    ];

    public function calculateReliability(): int
    {
        return (int) (100 * (
                (1.0 - ((currentTimeMillis() - $this->lastSeen) / 1296000000.0)) // calculates scalable factor | 15 days = 0
                * (min(25,
                        count($this->users)) / 25.0) // multiply the scalable factor based on the amount players | max = 25
            ));
    }

    public function shouldRemove(): bool
    {
        // 1296000000 is 15 days in milliseconds
        return ((currentTimeMillis() - $this->lastSeen) >= 1296000000);
    }

    public function getLocation(): array
    {
        return explode(':', $this->id);
    }
}
