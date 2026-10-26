<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Relay-read row (hades_voice_bans); all time columns are epoch milliseconds.
 *
 * @property int $id
 * @property string $user_id
 * @property string $reason
 * @property string $banned_by
 * @property int $banned_at
 * @property int|null $expires_at
 * @property int|null $lifted_at
 */
class VoiceBan extends Model
{
    use HasFactory;

    protected $table = 'hades_voice_bans';

    public $timestamps = false;

    protected $guarded = [];

    public function isActive(int $nowMillis): bool
    {
        if ($this->lifted_at !== null) {
            return false;
        }

        return $this->expires_at === null || $this->expires_at > $nowMillis;
    }

    public function scopeActive(Builder $query, int $nowMillis): void
    {
        $query->whereNull('lifted_at')
            ->where(function (Builder $q) use ($nowMillis) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>', $nowMillis);
            });
    }
}
