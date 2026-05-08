<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Guild>
 */
class GuildFactory extends Factory
{
    public function definition(): array
    {
        return [
            'id' => $this->faker->unique()->word(),
            'prefix' => strtoupper($this->faker->lexify('???')),
            'color' => '#'.$this->faker->regexify('[0-9a-f]{6}'),
        ];
    }
}
