<?php

namespace Database\Factories;

use App\Http\Enums\AccountType;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition()
    {
        return [
            '_id' => $this->faker->uuid(),
            'username' => $this->faker->userName(),
            'authToken' => Str::random(10),
            'password' => bcrypt('password'),
            'accountType' => $this->faker->randomElement(AccountType::cases()),
            'discordInfo' => [
                'username' => $this->faker->userName(),
                'id' => $this->faker->randomNumber()
            ],
            'cosmeticInfo' => [
                'capeTexture' => $this->faker->randomElement(['cape1', 'cape2', 'cape3']),
                'elytraEnabled' => $this->faker->boolean(),
                'maxResolution' => $this->faker->randomElement(['64x32', '128x64', '256x128', '512x256']),
                'allowAnimated' => $this->faker->boolean(),
                'parts' => [
                    'ears' => $this->faker->boolean()
                ]
            ]
        ];
    }
}
