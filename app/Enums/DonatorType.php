<?php

namespace App\Enums;

enum DonatorType: string
{
    case SPECIAL = 'Special';
    case PATREON_LEVEL_0 = 'Patreon Level 0';
    case PATREON_LEVEL_1 = 'Patreon Level 1';
    case PATREON_LEVEL_2 = 'Patreon Level 2';
    case PATREON_LEVEL_3 = 'Patreon Level 3';
    case PATREON_LEVEL_4 = 'Patreon Level 4';

    case NONE = 'None';

    public function getLevel(): int
    {
        return (int) str_replace('Patreon Level ', '', $this->value);
    }

    public static function fromPatreonLevel(string $level): self
    {
        return self::from('Patreon ' . $level);
    }
}
