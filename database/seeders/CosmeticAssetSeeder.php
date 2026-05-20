<?php

namespace Database\Seeders;

use App\Enums\CosmeticSlot;
use App\Enums\CosmeticStatus;
use App\Enums\CosmeticType;
use App\Enums\CosmeticVisibility;
use App\Models\CosmeticAsset;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Image;

class CosmeticAssetSeeder extends Seeder
{
    public function run(): void
    {
        $files = Storage::disk('approved')->files();

        foreach ($files as $sha) {
            // skip non-cape files
            if (!preg_match('/^[0-9a-f]{40}$/i', $sha)) {
                continue;
            }

            // idempotent: skip if already exists
            if (CosmeticAsset::bySha($sha)->exists()) {
                $this->command?->line("Skipping existing: {$sha}");

                continue;
            }

            // get image dimensions
            try {
                $image = Image::make(Storage::disk('approved')->path($sha));
                $width = $image->width();
                $height = $image->height();
            } catch (\Exception $e) {
                $this->command?->warn("Skipping unreadable file: {$sha} ({$e->getMessage()})");
                continue;
            }
            $animated = $height > ($width / 2);

            // compute tags
            $tags = ["size:{$width}"];
            if ($animated) {
                $tags[] = 'animated';
            }

            // uploader heuristic
            $wearers = User::byCapeTexture($sha)->get(['id']);
            $uploaderId = $wearers->count() === 1 ? $wearers->first()->id : null;
            $equipCount = $wearers->count();

            CosmeticAsset::create([
                'sha' => $sha,
                'type' => CosmeticType::TEXTURE,
                'slot' => CosmeticSlot::BACK,
                'status' => CosmeticStatus::APPROVED,
                'visibility' => CosmeticVisibility::PUBLIC,
                'uploader_id' => $uploaderId,
                'equip_count' => $equipCount,
                'width' => $width,
                'height' => $height,
                'tags' => $tags,
                'uploaded_at' => now(),
            ]);

            $this->command?->line("Seeded: {$sha} ({$width}x{$height}, equip_count={$equipCount})");
        }

        $this->command?->info('CosmeticAssetSeeder complete.');
    }
}
