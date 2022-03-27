<?php

namespace App\Http\Libraries;

use App\Http\Traits\Singleton;
use DiscordWebhook\EmbedColor;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class CapeManager
{
    // banCape, ApproveCape, queueCape, deleteCape, hasCape

    use Singleton;

    private \Illuminate\Contracts\Filesystem\Filesystem $queue;
    private \Illuminate\Contracts\Filesystem\Filesystem $banned;
    private \Illuminate\Contracts\Filesystem\Filesystem $approved;
    private string $token;

    public function __construct()
    {
        $this->queue = Storage::disk('queue');
        $this->banned = Storage::disk('banned');
        $this->approved = Storage::disk('approved');

        $this->token = config('athena.capes.token');
    }

    public function getCape($capeId): ?string
    {
        return $this->isApproved($capeId) ? $this->approved->path($capeId) : ($this->isBanned($capeId) ? $this->approved->path('bannedCape') : $this->approved->path('defaultCape'));
    }

    public function deleteCape(string $capeId): bool
    {
        if ($this->isApproved($capeId)) {
            return $this->approved->delete($capeId);
        }

        if($this->isQueued($capeId)) {
            return $this->queue->delete($capeId);
        }

        return false;
    }

    public function isApproved($capeId): bool
    {
        return $this->approved->exists($capeId);
    }

    public function getCapeAsBase64($capeId): ?string
    {
        return base64_encode($this->approved->get($capeId) ?? $this->approved->get('defaultCape'));
    }

    public function listCapes(): array
    {
        return collect($this->approved->files())->filter(static function ($item) {
            return $item !== '.gitignore';
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

    public function queueCape(UploadedFile $data): string|bool
    {
        $capeId = sha1_file($data->path());

        $data->storeAs('', $capeId, 'queue');

        Notifications::cape(
            title: "A new cape needs approval!",
            description: sprintf("➡️ **Choose:** [Approve](%s) or [Ban](%s)\n**SHA-1:** %s",
                url("capes/queue/approve/".$this->token."/".$capeId),
                url("capes/ban/".$this->token."/".$capeId),
                $capeId),
            color: EmbedColor::GOLD,
            imageUrl: url("capes/queue/get/$capeId")
        );

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
            color: EmbedColor::RED
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
}
