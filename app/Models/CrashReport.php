<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $trace_hash
 * @property string $trace
 * @property array $occurrences
 * @property array $comments
 * @property int $count
 * @property bool $handled
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 */
class CrashReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'trace_hash',
        'trace',
        'occurrences',
        'count',
        'handled',
    ];

    protected $casts = [
        'occurrences' => 'array',
        'comments' => 'array',
        'count' => 'integer',
        'handled' => 'boolean',
    ];

    public function getEarliestOccurrenceDate(): Carbon
    {
        $occurrence = $this->occurrences[0];

        return Carbon::parse($occurrence['time'] ?? $occurrence->time);
    }

    public function getLatestOccurrenceDate(): Carbon
    {
        $occurrence = $this->occurrences[count($this->occurrences) - 1];

        return Carbon::parse($occurrence['time'] ?? $occurrence->time);
    }

    public function getRouteKeyName(): string
    {
        return 'trace_hash';
    }
}
