<?php

namespace App\Models;

use Illuminate\Contracts\Database\Eloquent\Castable;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Support\Facades\Storage;

/**
 * @property string $capeTexture
 * @property string|null $elytraEnabled
 * @property string|null $maxResolution
 * @property string|null $allowAnimated
 * @property PartInfo|null $parts
 */
class CosmeticInfo implements Castable
{
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

    public static function castUsing(array $arguments)
    {
        return new class implements CastsAttributes
        {
            public function get($model, $key, $value, $attributes)
            {
                $info = new CosmeticInfo();
                $info->capeTexture = $value['capeTexture'] ?? null;
                $info->elytraEnabled = $value['elytraEnabled'] ?? null;
                $info->maxResolution = $value['maxResolution'] ?? null;
                $info->allowAnimated = $value['allowAnimated'] ?? null;
                $info->parts = $value['parts'] ?? null;
                return $info;
            }

            public function set($model, $key, $value, $attributes)
            {
                return [
                    'capeTexture' => $value->capeTexture,
                    'elytraEnabled' => $value->elytraEnabled,
                    'maxResolution' => $value->maxResolution,
                    'allowAnimated' => $value->allowAnimated,
                    'parts' => $value->parts,
                ];
            }
        };
    }
}
