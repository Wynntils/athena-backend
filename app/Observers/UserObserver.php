<?php

namespace App\Observers;

use App\Models\User;
use Illuminate\Support\Facades\Cache;

class UserObserver
{
    /**
     * Handle the User "updated" event.
     */
    public function updated(User $user): void
    {
        if (! $user->isDirty(['account_type', 'cosmetic_info'])) {
            return;
        }

        Cache::forget("user-{$user->id}");

        if ($user->isDirty('cosmetic_info')) {
            $oldCosmeticInfo = $user->getOriginal('cosmetic_info') ?? [];
            $oldTexture = $oldCosmeticInfo['capeTexture'] ?? 'defaultCape';

            Cache::forget("cape-texture-{$oldTexture}-1");
            Cache::forget("cape-texture-{$oldTexture}-0");
        }
    }
}
