<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * Relay-owned row (hades_voice_reports); all time columns are epoch milliseconds.
 *
 * @property int $id
 * @property string $reporter_id
 * @property string $target_id
 * @property string $reason
 * @property string $world
 * @property string $instance
 * @property float|null $reporter_x
 * @property float|null $reporter_y
 * @property float|null $reporter_z
 * @property float|null $target_x
 * @property float|null $target_y
 * @property float|null $target_z
 * @property string $witness_ids
 * @property int|null $audio_from
 * @property int|null $audio_to
 * @property string|null $reporter_audio_path
 * @property string|null $target_audio_path
 * @property int $created_at
 * @property bool $handled
 * @property string|null $handled_by
 * @property int|null $handled_at
 * @property int|null $discord_notified_at
 * @property array<int, array{id: int, user: string, user_id: string, time: string, comment: string}>|null $comments
 */
class VoiceReport extends Model
{
    use HasFactory;

    protected $table = 'hades_voice_reports';

    public $timestamps = false;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'handled' => 'boolean',
            'reporter_x' => 'float',
            'reporter_y' => 'float',
            'reporter_z' => 'float',
            'target_x' => 'float',
            'target_y' => 'float',
            'target_z' => 'float',
            'comments' => 'array',
        ];
    }

    public function createdAtCarbon(): Carbon
    {
        return Carbon::createFromTimestampMs($this->created_at);
    }

    public function handledAtCarbon(): ?Carbon
    {
        return $this->handled_at ? Carbon::createFromTimestampMs($this->handled_at) : null;
    }

    /** @return string[] */
    public function witnessIdArray(): array
    {
        return array_values(array_filter(explode(',', $this->witness_ids ?? '')));
    }

    public function hasReporterAudio(): bool
    {
        return $this->reporter_audio_path !== null;
    }

    public function hasTargetAudio(): bool
    {
        return $this->target_audio_path !== null;
    }

    public function scopeUnhandled(Builder $query): void
    {
        $query->where('handled', false);
    }
}
