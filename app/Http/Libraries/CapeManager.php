<?php

namespace App\Http\Libraries;

use App\Enums\MaskType;
use App\Http\Traits\Singleton;
use Carbon\Carbon;
use DiscordWebhook\EmbedColor;
use Illuminate\Support\Facades\Storage;
use Imagick;
use Intervention\Image\Facades\Image as ImageFactory;
use Intervention\Image\Image;

class CapeManager
{
    // array for special occasions on specific dates
    private array $specialCapes = [
        '01-01' => 'newYears',
        // TODO: New April fools cape (Having 2000 animated capes lags clients)
        //'04-01' => 'aprilFools',
        '07-04' => 'independenceDay',
        '10-31' => 'halloween',
        '12-24' => 'christmasEve',
        '12-25' => 'christmas',
        '12-31' => 'newYearsEve',
    ];

    use Singleton;

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
        if ($this->isApproved($capeId)) {
            return $this->approved->delete($capeId);
        }

        if ($this->isQueued($capeId)) {
            return $this->queue->delete($capeId);
        }

        return false;
    }

    public function isApproved($capeId): bool
    {
        if (empty($capeId)) {
            return false;
        }

        return $this->approved->exists($capeId);
    }

    public function isSpecialDate(): bool
    {
        return array_key_exists(Carbon::now()->format('m-d'), $this->specialCapes);
    }

    public function getSpecialCape(): string
    {
        return $this->special->get($this->specialCapes[Carbon::now()->format('m-d')]) ?? $this->special->get('defaultCape');
    }

    public function getCapeAsBase64($capeId): ?string
    {
        if ($this->isSpecialDate()) {
            return base64_encode($this->getSpecialCape());
        }

        return base64_encode($this->approved->get($capeId) ?? $this->special->get('defaultCape'));
    }

    public function listCapes(): array
    {
        ini_set('memory_limit', '-1');
        return collect($this->approved->files())->filter(static function ($item) {
            return $item !== '.gitignore';
        })->map(function ($item) {
            try {
                $image = ImageFactory::make($this->approved->path($item));
                return ['sha' => $item, 'width' => $image->getWidth(), 'height' => $image->getHeight()];
            } catch (\Throwable $e) {
                return ['sha' => $item, 'width' => 0, 'height' => 0];
            }
        })->values()->toArray();
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
        $imagick = new Imagick();

        $image->encode('png');

        $imagick->readImageBlob($image->getEncoded());

        return sha1($imagick->getImageSignature());
    }

    public function queueCape(Image $image, $notify = true): string|bool
    {
        $capeId = $this->getSha($image);

        $image->save($this->queue->path($capeId));

        if ($notify) {
            Notifications::cape(
                title: "A new cape needs approval!",
                description: sprintf("➡️ **Choose:** [Approve Full](%s) / [Approve Cape](%s) / [Approve Elytra](%s) or [Ban](%s)\n**SHA-1:** %s",
                    route('capes.queue.approve', ['token' => $this->token, 'sha' => $capeId, 'type' => 'full']),
                    route('capes.queue.approve', ['token' => $this->token, 'sha' => $capeId, 'type' => 'cape']),
                    route('capes.queue.approve', ['token' => $this->token, 'sha' => $capeId, 'type' => 'elytra']),
                    route('capes.ban', ['token' => $this->token, 'sha' => $capeId]),
                    $capeId),
                color: EmbedColor::GOLD,
                imageUrl: route('capes.queue.get', ['sha' => $capeId])
            );
        }

        return $capeId;
    }

    public function approveCape(string $capeId): void
    {
        if (!$this->isQueued($capeId)) {
            return;
        }

        $this->approved->put($capeId, $this->queue->get($capeId));
        $this->queue->delete($capeId);

        Notifications::cape(
            title: "A cape was approved",
            description: "➡️ **SHA-1**: $capeId",
            color: EmbedColor::GREEN,
            imageUrl: url("capes/get/$capeId")
        );
    }

    public function isQueued(string $capeId)
    {
        return $this->queue->exists($capeId);
    }

    public function banCape(string $capeId): void
    {
        if ($this->isBanned($capeId)) {
            return;
        }

        $this->banned->put($capeId, $this->getCape($capeId));
        $this->deleteCape($capeId);

        Notifications::cape(
            title: "A cape was banned",
            description: "➡️ **SHA-1**: $capeId",
            color: EmbedColor::RED,
            imageUrl: url("capes/queue/get/$capeId")
        );
    }

    public function isBanned(string $capeId)
    {
        return (bool) $this->banned->get($capeId);
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

        if(
            $mask->width() !== $image->width()
            && $mask->height() !== $image->height()
        ) {
            $mask->resize($image->width(), $image->width() / 2);
        }

        if($image->height() !== $image->width() / 2) {
            $mask = ImageFactory::canvas($image->width(), $image->height())->fill($mask);
        }

        $image->mask($mask, true);
    }

    public function deleteQueuedCape(string $capeId): void
    {
        $this->queue->delete($capeId);
    }
}
