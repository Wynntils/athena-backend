<?php

namespace App\Contracts\Libraries;

interface MinecraftFakeAuthInterface
{
    public function getPublicKey(): string;
    public function getGameProfile(string $username, string $key): ?array;
}
