<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Builder;
use Jenssegers\Mongodb\Eloquent\Model;

/**
 * @property string $prefix
 * @property string $color
 *
 * @mixin Builder
 */
class Guild extends Model
{

    public $timestamps = false;

    protected $fillable = [
        '_id',
        'prefix',
        'color'
    ];

    public static function gather(array $guild): static
    {
        if (empty($guild['guild'])) {
            return new Guild(['_id' => 'None', 'prefix' => 'NONE', 'color' => '#ffffff']);
        }

        return Guild::updateOrCreate(
            ['_id' => $guild['guild']],
            ['prefix' => $guild['guildPrefix']]
        );
    }

}
