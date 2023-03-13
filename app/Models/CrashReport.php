<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Carbon;
use Jenssegers\Mongodb\Eloquent\Model;

/**
 * @property string $trace_hash
 * @property string $trace
 * @property array $occurrences
 * @property int $count
 * @property bool $handled
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 *
 * @mixin Builder
 */
class CrashReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'trace_hash',
        'trace',
        'occurrences',
        'count',
    ];

    protected $casts = [
        'occurrences' => 'object',
    ];

    public function getEarliestOccurrenceDate(): Carbon
    {
        $occurrence = $this->occurrences[0];

        return Carbon::parse($occurrence->time);
    }

    public function getLatestOccurrenceDate(): Carbon
    {
        $occurrence = $this->occurrences[count($this->occurrences) - 1];

        return Carbon::parse($occurrence->time);
    }

    public function getRouteKeyName(): string
    {
        return 'trace_hash';
    }
}
