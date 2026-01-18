<?php

namespace App\Models;

use App\Enums\GatheringMaterial;
use App\Enums\ProfessionType;
use Illuminate\Database\Eloquent\Model;

/**
 * @property string $id
 * @property ProfessionType $type
 * @property GatheringMaterial $material
 * @property int $lastSeen
 * @property array $users
 */
class GatheringSpot extends Model
{
    protected $table = 'gathering_spots';

    public $timestamps = false;
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'type',
        'material',
        'lastSeen',
        'users',
    ];

    protected $casts = [
        'type' => ProfessionType::class,
        'material' => GatheringMaterial::class,
        'users' => 'array',
        'lastSeen' => 'integer',
    ];

    // Accessor for lastSeen (snake_case to camelCase)
    public function getLastSeenAttribute($value)
    {
        return $this->attributes['last_seen'] ?? $value;
    }

    // Mutator for lastSeen
    public function setLastSeenAttribute($value)
    {
        $this->attributes['last_seen'] = $value;
    }

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
