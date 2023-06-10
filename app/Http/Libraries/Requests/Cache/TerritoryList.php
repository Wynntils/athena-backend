<?php

namespace App\Http\Libraries\Requests\Cache;


use App\Models\Guild;
use Http;

class TerritoryList implements CacheContract
{

    public function refreshRate(): int
    {
        return 30;
    }

    public function generate(): array
    {
        $wynnTerritories = Http::wynn()->get(config('athena.api.wynn.territories'))->collect('territories');
        if ($wynnTerritories === null) {
            throw new \Exception('Failed to fetch territories from Wynn API');
        }

        return [
            'territories' => $wynnTerritories->map(static function ($item) {
                $guild = Guild::gather($item);

                $territory = [];
                $territory['territory'] = $item['territory'];
                $territory['guild'] = $item['guild'];
                $territory['guildPrefix'] = $item['guildPrefix'];
                $territory['guildColor'] = empty($guild->color) ? self::generateColor() : $guild->color;
                $territory['acquired'] = $item['acquired'];
                $territory['attacker'] = ""; // not used
                $territory['level'] = 1; // not used

                if (array_key_exists('location', $item)) {
                    $location = [];
                    $location['startX'] = $item['location']['startX'];
                    $location['startZ'] = $item['location']['startY'];
                    $location['endX'] = $item['location']['endX'];
                    $location['endZ'] = $item['location']['endY'];
                    $territory['location'] = $location;
                }

                return $territory;
            })->toArray()
        ];
    }

    private static function generateColorAndUpdate(Guild $guild): string
    {
        $crc32Value = crc32(time());
        $random = random_int(1, $crc32Value);
        $minS = 0.5;
        $minV = 0.75;

        $rgbaColor = fromHSV(
            $random / mt_getrandmax(),
            $minS + (1 - $minS) * ($random / mt_getrandmax()),
            $minV + (1 - $minV) * ($random / mt_getrandmax()),
            1
        );

        $hex = self::rgbaToHex($rgbaColor->r, $rgbaColor->g, $rgbaColor->b);

        $guild->update(['color' => $hex]);

        return $hex;
    }

    private static function rgbaToHex($red, $green, $blue, $alpha = 255): string
    {
        $redHex = dechex($red);
        $greenHex = dechex($green);
        $blueHex = dechex($blue);
        $alphaHex = dechex($alpha);

        $redHex = str_pad($redHex, 2, '0', STR_PAD_LEFT);
        $greenHex = str_pad($greenHex, 2, '0', STR_PAD_LEFT);
        $blueHex = str_pad($blueHex, 2, '0', STR_PAD_LEFT);
        $alphaHex = str_pad($alphaHex, 2, '0', STR_PAD_LEFT);

        return '#' . $redHex . $greenHex . $blueHex . $alphaHex;
    }
}
class CustomColor
{
    public $r;
    public $g;
    public $b;
    public $a;

    public function __construct($r, $g, $b, $a)
    {
        $this->r = $r;
        $this->g = $g;
        $this->b = $b;
        $this->a = $a;
    }
}

function clamp($value, $min, $max)
{
    return max($min, min($max, $value));
}

function fromHSV($h, $s, $v, $a)
{
    $a = clamp($a, 0, 1);
    if ($v <= 0) {
        return new CustomColor(0, 0, 0, $a);
    }
    if ($v > 1) {
        $v = 1;
    }
    if ($s <= 0) {
        return new CustomColor($v, $v, $v, $a);
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
            return new CustomColor($v, $v3, $v1, $a);
        case 1:
            return new CustomColor($v2, $v, $v1, $a);
        case 2:
            return new CustomColor($v1, $v, $v3, $a);
        case 3:
            return new CustomColor($v1, $v2, $v, $a);
        case 4:
            return new CustomColor($v3, $v1, $v, $a);
        default:
            return new CustomColor($v, $v1, $v2, $a);
    }
}



