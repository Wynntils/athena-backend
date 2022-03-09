<?php

namespace App\Models;

use App\Http\Libraries\Requests\WynnRequest;
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

    public static function gather(?string $name): Guild
    {
        if ($name === null) {
            return new Guild(['_id' => 'None', 'prefix' => 'NONE', 'color' => '#ffffff']);
        }

        $guild = Guild::find($name);

        if ($guild !== null) {
            return $guild;
        }

        $request = WynnRequest::request()->get(config('athena.api.wynn.guildInfo').$name)->json();
        if (array_key_exists('error', $request)) {
            return new Guild(['_id' => 'None', 'prefix' => 'ERROR', 'color' => '#ff0000']);
        }

        return Guild::create([
            '_id' => $name,
            'prefix' => $request['prefix'],
        ]);
    }

}
