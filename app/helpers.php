<?php
/**
 * Custom helpers
 */


if (!function_exists('cleanNull')) {
    function cleanNull(?array &$array)
    {
        if($array === null) {
            return;
        }
        $array = array_filter($array, static function ($a) {
            return !is_null($a);
        });
    }
}

if (!function_exists('getStatusType')) {
    function getStatusType(?string $raw): string
    {
        return match (true) {
            str_contains($raw, 'raw') => 'INTEGER',
            in_array($raw, ['manaRegen', 'lifeSteal', 'manaSteal']) => 'FOUR_SECONDS',
            $raw === 'poison' => 'THREE_SECONDS',
            $raw === 'attackSpeed' => 'TIER',
            default => 'PERCENTAGE',
        };
    }
}

if (!function_exists('ignoreZero')) {
    function ignoreZero($input)
    {
        if ($input === null) {
            return null;
        }
        if (is_numeric($input)) {
            return $input === 0 ? null : $input;
        }
        if (is_string($input)) {
            return empty($input) || $input === '0-0' ? null : $input;
        }
        return $input;
    }
}

if (!function_exists('currentTimeMillis')) {
    function currentTimeMillis(): int
    {
        return (int) \Carbon\Carbon::now()->getPreciseTimestamp(3);
    }
}
