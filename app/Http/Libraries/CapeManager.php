<?php

namespace App\Http\Libraries;

use App\Enums\CosmeticSlot;
use App\Enums\CosmeticStatus;
use App\Enums\CosmeticType;
use App\Enums\CosmeticVisibility;
use App\Enums\MaskType;
use App\Models\CosmeticAsset;
use App\Models\User;
use Carbon\Carbon;
use DiscordWebhook\EmbedColor;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Imagick;
use Intervention\Image\Facades\Image as ImageFactory;
use Intervention\Image\Image;

class CapeManager
{
    // TODO: If special cape doesn't exist, fallback on the users cape, also a setting to toggle this on the website.
    // array for special occasions on specific dates
    private array $specialCapes = [
        // '01-01' => 'newYears',
        // '04-01' => 'aprilFools',
        // '07-04' => 'independenceDay',
        // '10-31' => 'halloween',
        // '12-24' => 'christmasEve',
        // '12-25' => 'christmas',
        // '12-31' => 'newYearsEve',
    ];

    private \Illuminate\Contracts\Filesystem\Filesystem $queue;

    private \Illuminate\Contracts\Filesystem\Filesystem $banned;

    private \Illuminate\Contracts\Filesystem\Filesystem $approved;

    private \Illuminate\Contracts\Filesystem\Filesystem $special;

    private string $token;

    public function __construct()
    {
        $this->queue = Storage::disk('queue');
        $this->banned = Storage::disk('banned');
        $this->approved = Storage::disk('approved');
        $this->special = Storage::disk('special');

        $this->token = config('athena.capes.token');
    }

    public function getCape($capeId): ?string
    {
        return match (true) {
            $this->isApproved($capeId) => $this->approved->path($capeId),
            default => $this->special->path('defaultCape'),
        };
    }

    public function deleteCape(string $capeId): bool
    {
        $isApproved = $this->isApproved($capeId);
        $isQueued   = $this->isQueued($capeId);

        $deleted = false;
        if ($isApproved) $deleted = $this->approved->delete($capeId);
        elseif ($isQueued) $deleted = $this->queue->delete($capeId);

        CosmeticAsset::bySha($capeId)->delete();
        Cache::forget('capes.list');

        return $deleted;
    }

    public function isApproved($capeId): bool
    {
        if (empty($capeId)) return false;
        return CosmeticAsset::bySha($capeId)->where('status', CosmeticStatus::APPROVED)->exists();
    }

    public function isSpecialDate(): bool
    {
        return array_key_exists(Carbon::now()->format('m-d'), $this->specialCapes);
    }

    public function getSpecialCape(): string
    {
        return $this->special->get($this->specialCapes[Carbon::now()->format('m-d')]) ?? $this->special->get('defaultCape');
    }

    public function getCapeAsBase64($capeId, $omitDefaultCape): ?string
    {
        if ($this->isSpecialDate()) {
            return base64_encode($this->getSpecialCape());
        }

        $cacheKey = 'cape-texture-'.$capeId.'-'.($omitDefaultCape ? '1' : '0');

        return Cache::remember($cacheKey, 2592000, function () use ($capeId, $omitDefaultCape) {
            if ($omitDefaultCape) {
                return base64_encode($this->approved->get($capeId));
            }

            return base64_encode($this->approved->get($capeId) ?? $this->special->get('defaultCape'));
        });
    }

    public function listCapes(): array
    {
        return Cache::remember('capes.list', 86400, function () {
            return CosmeticAsset::where('status', CosmeticStatus::APPROVED)
                ->where('slot', CosmeticSlot::BACK)
                ->where('type', CosmeticType::TEXTURE)
                ->get(['sha', 'width', 'height'])
                ->map(fn($a) => [
                    'sha'      => $a->sha,
                    'width'    => $a->width,
                    'height'   => $a->height,
                    'animated' => $a->isAnimated(),
                ])
                ->toArray();
        });
    }

    public function getQueuedCape($capeId): ?string
    {
        return $this->isQueued($capeId) ? $this->queue->path($capeId) : $this->getCape($capeId);
    }

    public function listQueuedCapes(): array
    {
        return collect($this->queue->files())->filter(static function ($item) {
            return $item !== '.gitignore';
        })->values()->toArray();
    }

    public function getSha(Image $image): string
    {
        $imagick = new Imagick;

        $image->encode('png');

        $imagick->readImageBlob($image->getEncoded());

        return sha1($imagick->getImageSignature());
    }

    public function queueCape(Image $image, string $username, bool $notify = true, ?User $uploader = null, array $metadata = []): string
    {
        $capeId = $this->getSha($image);
        $image->save($this->queue->path($capeId));

        CosmeticAsset::firstOrCreate(['sha' => $capeId], [
            'type'        => CosmeticType::TEXTURE,
            'slot'        => CosmeticSlot::BACK,
            'status'      => CosmeticStatus::QUEUED,
            'uploader_id' => $uploader?->id,
            'name'        => $metadata['name'] ?? null,
            'visibility'  => CosmeticVisibility::tryFrom($metadata['visibility'] ?? '') ?? CosmeticVisibility::PUBLIC,
            'tags'        => array_values(array_unique(array_slice(array_map('strtolower', array_filter($metadata['tags'] ?? [])), 0, 10))),
            'uploaded_at' => now(),
        ]);

        if ($notify) {
            Notifications::cape(
                title: 'A new cape needs approval!',
                description: sprintf(
                    "**Uploaded by:** %s\n**Name:** %s\n➡️ **Choose:** [Approve Full](%s) / [Approve Cape](%s) / [Approve Elytra](%s) or [Ban](%s)\n**SHA-1:** %s",
                    $username,
                    $metadata['name'] ?? '(none)',
                    route('capes.queue.approve', ['token' => $this->token, 'sha' => $capeId, 'type' => 'full']),
                    route('capes.queue.approve', ['token' => $this->token, 'sha' => $capeId, 'type' => 'cape']),
                    route('capes.queue.approve', ['token' => $this->token, 'sha' => $capeId, 'type' => 'elytra']),
                    route('capes.ban', ['token' => $this->token, 'sha' => $capeId]),
                    $capeId
                ),
                color: EmbedColor::GOLD,
                imageUrl: route('capes.queue.get', ['sha' => $capeId])
            );
        }

        return $capeId;
    }

    public function approveCape(string $capeId): void
    {
        if (!$this->isQueued($capeId)) return;

        $this->approved->put($capeId, $this->queue->get($capeId));
        $this->queue->delete($capeId);

        $image    = ImageFactory::make($this->approved->path($capeId));
        $width    = $image->width();
        $height   = $image->height();
        $animated = $height > ($width / 2);

        $systemTags = ["size:{$width}"];
        if ($animated) $systemTags[] = 'animated';

        $asset = CosmeticAsset::bySha($capeId)->first();
        if ($asset) {
            $userTags = array_filter($asset->tags ?? [], fn($t) => !str_starts_with($t, 'size:') && $t !== 'animated');
            $asset->update([
                'status' => CosmeticStatus::APPROVED,
                'width'  => $width,
                'height' => $height,
                'tags'   => array_values(array_unique(array_merge($systemTags, $userTags))),
            ]);
        } else {
            \Log::warning("approveCape: no cosmetic_assets row found for SHA {$capeId} — file approved but DB not updated");
        }

        Cache::forget("cape-texture-{$capeId}-1");
        Cache::forget("cape-texture-{$capeId}-0");
        Cache::forget('capes.list');

        Notifications::cape(
            title: 'A cape was approved',
            description: "➡️ **SHA-1**: $capeId",
            color: EmbedColor::GREEN,
            imageUrl: url("capes/get/$capeId")
        );
    }

    public function isQueued(string $capeId): bool
    {
        return CosmeticAsset::bySha($capeId)->where('status', CosmeticStatus::QUEUED)->exists();
    }

    public function banCape(string $capeId): void
    {
        if ($this->isBanned($capeId)) return;

        $isCurrentlyApproved = $this->isApproved($capeId);
        $isCurrentlyQueued   = $this->isQueued($capeId);

        if ($isCurrentlyApproved) {
            $this->banned->put($capeId, $this->approved->get($capeId));
            $this->approved->delete($capeId);
        } elseif ($isCurrentlyQueued) {
            $this->banned->put($capeId, $this->queue->get($capeId));
            $this->queue->delete($capeId);
        }

        CosmeticAsset::bySha($capeId)->update(['status' => CosmeticStatus::BANNED]);

        Cache::forget("cape-texture-{$capeId}-1");
        Cache::forget("cape-texture-{$capeId}-0");
        Cache::forget('capes.list');

        Notifications::cape(
            title: 'A cape was banned',
            description: "➡️ **SHA-1**: $capeId",
            color: EmbedColor::RED,
            imageUrl: url("capes/queue/get/$capeId")
        );
    }

    public function isBanned(string $capeId): bool
    {
        return CosmeticAsset::bySha($capeId)->where('status', CosmeticStatus::BANNED)->exists();
    }

    public function getToken(): string
    {
        return $this->token;
    }

    public function maskCapeImage(Image $image, MaskType $type = MaskType::FULL): void
    {
        $maskImage = match ($type) {
            MaskType::FULL => Storage::get('full-mask.png'),
            MaskType::CAPE => Storage::get('cape-mask.png'),
            MaskType::ELYTRA => Storage::get('elytra-mask.png'),
        };

        $mask = ImageFactory::make($maskImage);

        if (
            $mask->width() !== $image->width()
            && $mask->height() !== $image->height()
        ) {
            $mask->resize($image->width(), $image->width() / 2);
        }

        if ($image->height() !== $image->width() / 2) {
            $mask = ImageFactory::canvas($image->width(), $image->height())->fill($mask);
        }

        $image->mask($mask, true);
    }

    public function deleteQueuedCape(string $capeId): void
    {
        $this->queue->delete($capeId);
        CosmeticAsset::bySha($capeId)->where('status', CosmeticStatus::QUEUED)->delete();
    }
}
