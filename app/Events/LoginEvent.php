<?php

namespace App\Events;

use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;

class LoginEvent
{
    use Dispatchable;

    public function __construct(public User $user, public string $userAgent, public string $method)
    {
    }
}
