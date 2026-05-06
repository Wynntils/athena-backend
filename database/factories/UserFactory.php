<?php

namespace Database\Factories;

use App\Enums\AccountType;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition(): array
    {
        return [
            'id'            => Str::uuid()->toString(),
            'username'      => $this->faker->userName(),
            'account_type'  => AccountType::NORMAL,
            'auth_token'    => Str::uuid()->toString(),
            'cosmetic_info' => [],
        ];
    }
}
