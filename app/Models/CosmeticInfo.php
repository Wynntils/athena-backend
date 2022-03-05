<?php

namespace App\Models;

use Jenssegers\Mongodb\Eloquent\Model;

/**
 * @property string|null $capeTexture
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
}
