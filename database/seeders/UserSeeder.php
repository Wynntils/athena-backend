<?php

namespace Database\Seeders;

use App\Enums\AccountType;
use Faker\Generator;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function __construct(public Generator $faker)
    {
    }

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->createUser('Scyu_', '879be29a-bcca-43d6-978a-321a4241c392', AccountType::MODERATOR);
        $this->createUser('Chr_omium', 'efcfc6dd-6fab-4717-b324-950165a18474', AccountType::MODERATOR);
        $this->createUser('Koala', '9fa9dbd5-fa33-4345-ab5d-62fa8c6dcfac', AccountType::NORMAL);
        $this->createUser('KrissaNXD', '254c3e64-30cc-497d-8ceb-f46e4478ca53', AccountType::NORMAL);
    }

    private function createUser($username, $uuid, $accountType) {
        \App\Models\User::create([
            '_id' => $uuid,
            'username' => $username,
            'authToken' => $this->faker->uuid(),
            'password' => bcrypt('password'),
            'accountType' => $accountType,
            'discordInfo' => [
                'username' => $username,
                'id' => 1234567890
            ],
            'cosmeticInfo' => [
                'capeTexture' => \Hash::make($this->faker->randomAscii()),
                'elytraEnabled' => $this->faker->boolean(),
                'maxResolution' => $this->faker->randomElement(['64x32', '128x64', '256x128', '512x256']),
                'allowAnimated' => $this->faker->boolean(),
                'parts' => [
                    'ears' => $this->faker->boolean()
                ]
            ]
        ]);
    }
}
