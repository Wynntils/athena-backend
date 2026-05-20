<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CosmeticVote extends Model
{
    protected $fillable = ['cosmetic_id', 'user_id', 'vote'];

    protected $casts = ['vote' => 'integer'];

    public function cosmetic(): BelongsTo
    {
        return $this->belongsTo(CosmeticAsset::class, 'cosmetic_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
