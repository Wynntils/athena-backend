<?php

namespace App\Models;

use App\Enums\CosmeticSlot;
use App\Enums\CosmeticStatus;
use App\Enums\CosmeticType;
use App\Enums\CosmeticVisibility;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class CosmeticAsset extends Model
{
    use HasFactory;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'sha', 'type', 'slot', 'status', 'uploader_id', 'name',
        'visibility', 'tags', 'width', 'height', 'equip_count',
        'uploaded_at', 'pending_name', 'pending_visibility', 'pending_tags',
    ];

    protected $attributes = [
        'equip_count' => 0,
        'tags'        => '[]',
    ];

    protected $casts = [
        'type'               => CosmeticType::class,
        'slot'               => CosmeticSlot::class,
        'status'             => CosmeticStatus::class,
        'visibility'         => CosmeticVisibility::class,
        'pending_visibility' => CosmeticVisibility::class,
        'tags'               => 'array',
        'pending_tags'       => 'array',
        'uploaded_at'        => 'datetime',
        'equip_count'        => 'integer',
        'width'              => 'integer',
        'height'             => 'integer',
    ];

    protected static function boot(): void
    {
        parent::boot();
        static::creating(function (self $model) {
            if (empty($model->id)) {
                $model->id = (string) Str::uuid();
            }
        });
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploader_id');
    }

    public function votes(): HasMany
    {
        return $this->hasMany(CosmeticVote::class, 'cosmetic_id');
    }

    public function scopeApprovedPublic(Builder $query): void
    {
        $query->where('status', CosmeticStatus::APPROVED)
            ->where('visibility', CosmeticVisibility::PUBLIC);
    }

    public function scopeBySha(Builder $query, string $sha): void
    {
        $query->where('sha', $sha);
    }

    public function isAnimated(): bool
    {
        return $this->height !== null && $this->width !== null && $this->height > ($this->width / 2);
    }

    public function hasPendingEdit(): bool
    {
        return $this->pending_name !== null
            || $this->pending_visibility !== null
            || $this->pending_tags !== null;
    }
}
