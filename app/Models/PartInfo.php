<?php

namespace App\Models;

use Illuminate\Contracts\Database\Eloquent\Castable;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;

/**
 * @property bool|null $ears
 */
class PartInfo implements Castable
{
    /**
     * @var array<int, string>
     */
    protected array $casts = [
        'ears' => 'bool'
    ];

    public static function castUsing(array $arguments)
    {
        return new class implements CastsAttributes
        {
            public function get($model, $key, $value, $attributes)
            {
                $info = new PartInfo();
                $info->ears = $value;
                return $info;
            }

            public function set($model, $key, $value, $attributes)
            {
                return [
                    'ears' => $value->ears
                ];
            }
        };
    }
}
