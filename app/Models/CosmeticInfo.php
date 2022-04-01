<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Jenssegers\Mongodb\Eloquent\Model;

/**
 * @property string $capeTexture
 * @property string|null $elytraEnabled
 * @property string|null $maxResolution
 * @property string|null $allowAnimated
 * @property PartInfo|null $parts
 */
class CosmeticInfo extends Model
{
    public function hasCape(): bool
    {
        if (Carbon::now()->format('m-d') === '04-01') {
            return true;
        }

        return !$this->elytraEnabled && $this->isTextureValid();
    }

    private function isTextureValid(): bool
    {
        return !empty($this->capeTexture) && Storage::disk('approved')->exists($this->capeTexture);
    }

    public function hasPart($part): bool
    {
        return !empty($this->parts) && $this->parts[$part] === true;
    }

    public function hasElytra(): bool
    {
        if (Carbon::now()->format('m-d') === '04-01') {
            return false;
        }

        return $this->elytraEnabled && $this->isTextureValid();
    }

    public function getFormattedTexture(): string
    {
        return $this->isTextureValid() ? $this->capeTexture : 'defaultCape';
    }
}
