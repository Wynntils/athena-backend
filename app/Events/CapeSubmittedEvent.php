<?php

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;

class CapeSubmittedEvent
{
    use Dispatchable;

    public function __construct(public string $username) {}
}
