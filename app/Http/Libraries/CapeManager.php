<?php

namespace App\Http\Libraries;

use App\Http\Traits\Singleton;
use Illuminate\Support\Facades\Storage;

class CapeManager
{

    use Singleton;

    private \Illuminate\Contracts\Filesystem\Filesystem $capes;

    public function __construct()
    {
        $this->capes = Storage::disk('capes');
    }

    public function getCape($capeId): ?string
    {

        return $this->capes->get('approved/'.$capeId) ?? $this->capes->get('approved/defaultCape');
    }

    public function getCapeAsBase64($capeId): ?string
    {

        return base64_encode($this->capes->get('approved/'.$capeId) ?? $this->capes->get('approved/defaultCape'));
    }

    public function listCapes(): array
    {
        return collect($this->capes->files('approved'))->map(static function ($item) {
            return str_replace("approved/", "", $item);
        })->filter(static function ($item) {
            return $item !== '.gitignore';
        })->values()->toArray();
    }
}
