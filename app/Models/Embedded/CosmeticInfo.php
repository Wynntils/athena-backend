<?php

namespace App\Models\Embedded;

use App\Http\Libraries\CapeManager;
use Illuminate\Support\Facades\Storage;
use Jenssegers\Mongodb\Eloquent\Model;

/**
 * @property string $capeTexture
 * @property bool|null $elytraEnabled
 * @property string|null $maxResolution
 * @property bool|null $allowAnimated
 * @property PartInfo|null $parts
 */
class CosmeticInfo extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'capeTexture',
        'elytraEnabled',
        'maxResolution',
        'allowAnimated',
        'parts',
    ];

    protected $casts = [
        'elytraEnabled' => 'bool',
        'allowAnimated' => 'bool'
    ];

    public function hasCape(): bool
    {
        if (CapeManager::instance()->isSpecialDate()) {
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
        return !empty($this->parts) && $this->parts->{$part} === true;
    }

    public function hasElytra(): bool
    {
        if (CapeManager::instance()->isSpecialDate()) {
            return false;
        }

        return $this->elytraEnabled && $this->isTextureValid();
    }

    public function getFormattedTexture(): string
    {
        return $this->isTextureValid() ? $this->capeTexture : 'defaultCape';
    }

    public function parts(): \Jenssegers\Mongodb\Relations\EmbedsOne
    {
        return $this->embedsOne(PartInfo::class);
    }
}
