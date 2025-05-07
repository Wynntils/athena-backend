<?php

namespace App\Models;

use App\Enums\CosmeticModel;
use App\Models\Traits\HasManyUsers;
use MongoDB\Laravel\Eloquent\Builder;
use MongoDB\Laravel\Eloquent\Model;

/**
 * @property CosmeticModel $model
 * @property string $data
 * @property string $texture
 * @property string $hash
 * @property array $users
 * @property Decision $decision
 *
 * @mixin Builder
 */
class CosmeticPart extends Model
{
    use HasManyUsers;

    public $timestamps = false;
    protected $fillable = [
        'model',
        'data',
        'texture',
        'hash',
        'uploadedBy'
    ];
    protected $casts = [
        'model' => CosmeticModel::class,
    ];

    public function decision()
    {
        return $this->embedsOne(Decision::class);
    }

    public function getRouteKeyName()
    {
        return 'hash';
    }
}
