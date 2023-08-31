<?php
/**
 * Custom helpers
 */


if (!function_exists('cleanNull')) {
    function cleanNull(?array $array): ?object
    {
        if($array === null) {
            return null;
        }
        return (object) array_filter($array, static function ($a) {
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

if (!function_exists('humanFileSize')) {
    function humanFileSize($bytes, $decimals = 2): string
    {
        $size = ['B', 'kB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];
        $factor = floor((strlen($bytes) - 1) / 3);
        return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . @$size[$factor];
    }
}

if(!function_exists("hsvToRgb")) {
    function hsvToRgb(float $h, float $s, float $v) {
        // com.wynntils.utils.colors.CustomColor#fromHSV(float,float,float,float)
        if ($v <= 0) {
            return array(0, 0, 0);
        }
        if ($v > 1) {
            $v = 1;
        }
        if ($s <= 0) {
            return array($v, $v, $v);
        }
        if ($s > 1) {
            $s = 1;
        }

        $vh = (($h % 1 + 1) * 6) % 6;

        $vi = floor($vh);
        $v1 = $v * (1 - $s);
        $v2 = $v * (1 - $s * ($vh - $vi));
        $v3 = $v * (1 - $s * (1 - ($vh - $vi)));

        switch ($vi) {
            case 0:
                return array($v, $v3, $v1);
            case 1:
                return array($v2, $v, $v1);
            case 2:
                return array($v1, $v, $v3);
            case 3:
                return array($v1, $v2, $v);
            case 4:
                return array($v3, $v1, $v);
            case 5:
                return array($v, $v1, $v2);
        }
    }
}


if(!function_exists("randomFloat")) {
    function randomFloat() {
        return random_int(0, mt_getrandmax() - 1) / mt_getrandmax();
    }
}

if(!function_exists("colourFromName")) {
    function colourFromName(string $name) {
        $minS = .5;
        $minV = .75;
        $seed = hexdec(hash("crc32", $name));
        mt_srand($seed);
        return hsvToRgb(
            randomFloat(),
            $minS + (1 - $minS) * randomFloat(),
            $minV + (1 - $minV) * randomFloat()
        );
    }
}

if(!function_exists("rgbFloatsToHex")) {
    function rgbFloatsToHex(array $rgb) {
        return "#" . dechex(round($rgb[0] * 255)) . dechex(round($rgb[1] * 255)) . dechex(round($rgb[2] * 255));
    }
}
