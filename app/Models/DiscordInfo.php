<?php

namespace App\Models;

use Illuminate\Contracts\Database\Eloquent\Castable;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;

/**
 * @property $id
 * @property $username
 */
class DiscordInfo implements Castable
{
    public static function castUsing(array $arguments)
    {
        return new class implements CastsAttributes
        {
            public function get($model, $key, $value, $attributes)
            {
                $info = new DiscordInfo();
                $info->username = $value['username'];
                $info->id = $value['id'];
                return $info;
            }

            public function set($model, $key, $value, $attributes)
            {
                return [
                    'username' => $value->username,
                    'id' => $value->id
                ];
            }
        };
    }
}
