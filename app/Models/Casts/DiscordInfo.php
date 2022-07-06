<?php

namespace App\Models\Casts;

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
        return new class implements CastsAttributes {
            public function get($model, $key, $value, $attributes)
            {
                $info = new DiscordInfo();
                $info->username = $value['username'] ?? null;
                $info->id = $value['id'] ?? null;
                return $info;
            }

            public function set($model, $key, $value, $attributes)
            {
                return [
                    'discordInfo' => [
                        'username' => $value->username,
                        'id' => $value->id
                    ]
                ];
            }
        };
    }
}
