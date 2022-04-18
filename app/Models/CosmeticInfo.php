<?php

namespace App\Models;

use Carbon\Carbon;
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

    public static function castUsing(array $arguments)
    {
        return new class implements CastsAttributes {
            public function get($model, $key, $value, $attributes)
            {
                $info = new CosmeticInfo();
                $info->capeTexture = $value['capeTexture'] ?? null;
                $info->elytraEnabled = $value['elytraEnabled'] ?? null;
                $info->maxResolution = $value['maxResolution'] ?? null;
                $info->allowAnimated = $value['allowAnimated'] ?? null;
                $info->parts = new PartInfo($value['parts']['ears'] ?? null);
                return $info;
            }

            public function set($model, $key, $value, $attributes)
            {
                return [
                    'cosmeticInfo' => [
                        'capeTexture' => $value->capeTexture,
                        'elytraEnabled' => $value->elytraEnabled,
                        'maxResolution' => $value->maxResolution,
                        'allowAnimated' => $value->allowAnimated,
                        'parts' => $value->parts,
                    ]
                ];
            }
        };
    }
}
