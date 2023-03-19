<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class CrashReportFactory extends Factory
{
    public function definition(): array
    {
        $occurrences = [];

        for($i = 0; $i < $this->faker->randomNumber(); $i++) {
            $occurrences[] = [
                'version' => 'v'.$this->faker->semver(false, true),
                'time' => Carbon::now(),
                'user_agent' => $this->faker->userAgent(),
            ];
        }

        return [
            'trace_hash' => $this->faker->md5(),
            'trace' => $this->faker->paragraphs(5, true),
            'occurrences' => $occurrences,
            'count' => $this->faker->randomNumber(),
            'handled' => $this->faker->boolean(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];
    }
}
