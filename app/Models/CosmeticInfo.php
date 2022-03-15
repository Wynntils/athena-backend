<?php

namespace App\Models;

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
    public function parts(): \Jenssegers\Mongodb\Relations\EmbedsOne
    {
        return $this->embedsOne(PartInfo::class);
    }

    public function hasCape(): bool
    {
        return !$this->elytraEnabled && $this->isTextureValid();
    }

    private function isTextureValid(): bool
    {
        return !empty($this->capeTexture) && Storage::disk('capes')->exists('approved/'.$this->capeTexture);
    }

    public function hasPart($part): bool
    {
        return !empty($this->parts) && $this->parts->{$part} === true;
    }

    public function hasElytra(): bool
    {
        return $this->elytraEnabled && $this->isTextureValid();
    }

    public function getFormattedTexture(): string
    {
        return $this->isTextureValid() ? $this->capeTexture : 'defaultCape';
    }
}
