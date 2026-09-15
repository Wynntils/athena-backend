<?php

namespace Database\Factories;

use App\Models\VoiceReport;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class VoiceReportFactory extends Factory
{
    protected $model = VoiceReport::class;

    public function definition(): array
    {
        return [
            'reporter_id' => Str::uuid()->toString(),
            'target_id' => Str::uuid()->toString(),
            'reason' => $this->faker->sentence(),
            'world' => 'EU'.$this->faker->numberBetween(1, 40),
            'instance' => '',
            'witness_ids' => '',
            'created_at' => now()->getTimestampMs(),
            'handled' => false,
        ];
    }
}
