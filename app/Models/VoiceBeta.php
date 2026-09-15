<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Closed-beta allowlist read by the relay (hades_voice_beta).
 *
 * @property string $user_id
 */
class VoiceBeta extends Model
{
    protected $table = 'hades_voice_beta';

    protected $primaryKey = 'user_id';

    protected $keyType = 'string';

    public $incrementing = false;

    public $timestamps = false;

    protected $guarded = [];
}
