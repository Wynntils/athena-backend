<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property string $id
 * @property string $name
 * @property string $description
 * @property array $adminContact
 * @property int $maxLimit
 * @property array $dailyRequests
 */
class ApiKey extends Model
{
    protected $table = 'api_keys';

    public $timestamps = false;
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'name',
        'description',
        'adminContact',
        'maxLimit',
        'dailyRequests',
    ];

    protected $casts = [
        'admin_contact' => 'array',
        'daily_requests' => 'array',
        'max_limit' => 'integer',
    ];

    // Accessor for adminContact (snake_case to camelCase)
    public function getAdminContactAttribute($value)
    {
        return $this->attributes['admin_contact'] ?? $value;
    }

    // Mutator for adminContact
    public function setAdminContactAttribute($value)
    {
        $this->attributes['admin_contact'] = $value;
    }

    // Accessor for maxLimit
    public function getMaxLimitAttribute($value)
    {
        return $this->attributes['max_limit'] ?? $value;
    }

    // Mutator for maxLimit
    public function setMaxLimitAttribute($value)
    {
        $this->attributes['max_limit'] = $value;
    }

    // Accessor for dailyRequests
    public function getDailyRequestsAttribute($value)
    {
        return $this->attributes['daily_requests'] ?? $value;
    }

    // Mutator for dailyRequests
    public function setDailyRequestsAttribute($value)
    {
        $this->attributes['daily_requests'] = $value;
    }
}
