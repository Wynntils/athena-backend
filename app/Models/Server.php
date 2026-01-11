<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property string $id
 * @property int $firstSeen
 */
class Server extends Model
{
    public $timestamps = false;
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'firstSeen'
    ];

    protected $casts = [
        'first_seen' => 'integer',
    ];

    // Accessor for firstSeen (snake_case to camelCase)
    public function getFirstSeenAttribute($value)
    {
        return $this->attributes['first_seen'] ?? $value;
    }

    // Mutator for firstSeen
    public function setFirstSeenAttribute($value)
    {
        $this->attributes['first_seen'] = $value;
    }
}
