<?php

namespace Database\Factories;

use App\Models\VoiceBan;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class VoiceBanFactory extends Factory
{
    protected $model = VoiceBan::class;

    public function definition(): array
    {
        return [
            'user_id' => Str::uuid()->toString(),
            'reason' => $this->faker->sentence(),
            'banned_by' => 'staff',
            'banned_at' => now()->getTimestampMs(),
        ];
    }
}
