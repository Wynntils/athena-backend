<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property string $id
 * @property string $prefix
 * @property string $color
 */
class Guild extends Model
{
    public $timestamps = false;
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'prefix',
        'color'
    ];

    private static function normalizeGuildData(array $guild): ?array
    {
        $data = $guild['guild'] ?? $guild;

        if (is_string($data)) {
            $data = [
                'name' => $data,
                'prefix' => $guild['prefix'] ?? null,
            ];
        }

        if (!is_array($data)) {
            return null;
        }

        $name = data_get($data, 'name');
        $prefix = data_get($data, 'prefix');

        if (($name === null || $name === '') && ($prefix === null || $prefix === '')) {
            return null;
        }

        return [
            'name' => $name ?? $prefix,
            'prefix' => $prefix ?? 'NONE',
        ];
    }

    public static function gather(array $guild): static
    {
        $normalized = self::normalizeGuildData($guild);

        if ($normalized === null) {
            return new Guild(['id' => 'None', 'prefix' => 'NONE', 'color' => '#ffffff']);
        }

        /** @var Guild $result */
        $result = Guild::updateOrCreate(
            ['id' => $normalized['name']],
            ['prefix' => $normalized['prefix']]
        );

        return $result;
    }

}
