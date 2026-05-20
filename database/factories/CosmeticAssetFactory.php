<?php

namespace Database\Factories;

use App\Enums\CosmeticSlot;
use App\Enums\CosmeticStatus;
use App\Enums\CosmeticType;
use App\Enums\CosmeticVisibility;
use App\Models\CosmeticAsset;
use Illuminate\Database\Eloquent\Factories\Factory;

class CosmeticAssetFactory extends Factory
{
    protected $model = CosmeticAsset::class;

    public function definition(): array
    {
        return [
            'sha'         => sha1($this->faker->unique()->uuid()),
            'type'        => CosmeticType::TEXTURE,
            'slot'        => CosmeticSlot::BACK,
            'status'      => CosmeticStatus::QUEUED,
            'uploader_id' => null,
            'name'        => null,
            'visibility'  => CosmeticVisibility::PUBLIC,
            'tags'        => [],
            'width'       => null,
            'height'      => null,
            'equip_count' => 0,
            'uploaded_at' => now(),
        ];
    }

    public function approved(): self
    {
        return $this->state(['status' => CosmeticStatus::APPROVED, 'width' => 64, 'height' => 32]);
    }
}
