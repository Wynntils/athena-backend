<?php

namespace App\Http\Requests;

class CapeRequest extends BaseRequest
{
    public function uploadCape(): array
    {
        return [
            'cape' => 'required|file|mimes:png|max:500',
        ];
    }

    public function delete(): array {
        return [
            'sha-1' => 'required|string|size:40',
        ];
    }

}
