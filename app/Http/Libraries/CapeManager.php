<?php

namespace App\Http\Libraries;

use App\Http\Traits\Singleton;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class CapeManager
{
    // banCape, ApproveCape, queueCape, deleteCape, hasCape

    use Singleton;

    private \Illuminate\Contracts\Filesystem\Filesystem $queue;
    private \Illuminate\Contracts\Filesystem\Filesystem $banned;
    private \Illuminate\Contracts\Filesystem\Filesystem $approved;

    public function __construct()
    {
        $this->queue = Storage::disk('queue');
        $this->banned = Storage::disk('banned');
        $this->approved = Storage::disk('approved');
    }

    public function getCape($capeId): ?string
    {

        return $this->approved->get($capeId) ?? $this->approved->get('defaultCape');
    }

    public function hasCape(string $sha1): bool
    {
        return $this->isApproved($sha1);
    }

    public function deleteCape(string $sha1): bool
    {
        if (!$this->hasCape($sha1))
        {
            return false;
        }

        return $this->approved->delete($sha1);
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
        return $this->queue->get($capeId) ?? $this->approved->get('defaultCape');
    }

    public function listQueuedCapes(): array
    {
        return collect($this->queue->files())->filter(static function ($item) {
            return $item !== '.gitignore';
        })->values()->toArray();
    }


    public function queueCape(UploadedFile $data): void
    {
        $sha1 = sha1_file($data->path());

        $data->storeAs('', $sha1, 'queue');

        // TODO: Notifaction - Webhook Confirmation
    }

    public function approveCape(string $capeId): void
    {
        if (!$this->isQueued($capeId)) {
            return;
        }

        $this->approved->put($capeId, $this->queue->get($capeId));
        $this->queue->delete($capeId);

        // TODO: Notifaction - Queue Message
    }

    public function banCape(string $capeId): void
    {
        if (!$this->isQueued($capeId)) {
            return;
        }

        $this->banned->put($capeId, $this->queue->get($capeId));
        $this->queue->delete($capeId);

        // TODO: Notifaction - Queue Message
    }

    public function isApproved($capeId): bool
    {
        return (bool) $this->approved->get($capeId);
    }

    public function isBanned(string $capeId)
    {
        return (bool) $this->banned->get($capeId);
    }

    public function isQueued(string $capeId)
    {
        return (bool) $this->queue->get($capeId);
    }




    /* MaskCape is to be ignored - Scyu */
}
