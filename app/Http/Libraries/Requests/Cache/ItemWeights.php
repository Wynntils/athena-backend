<?php

namespace App\Http\Libraries\Requests\Cache;

use Illuminate\Http\Client\Pool;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ItemWeights implements CacheContract
{
    public function refreshRate(): int
    {
        return 3600;
    }

    public function generate(): array
    {
        /** @var Response[] $responses */
        $responses = Http::wynn()->pool(fn (Pool $pool) => [
            $pool->as('wynnpoolWeights')->get(config('athena.api.wynnpool.itemWeights')),
            $pool->as('noriWeights')->get(config('athena.api.nori.itemWeights')),
        ]);

        $this->ensureOk($responses['wynnpoolWeights'] ?? null, 'wynnpool.itemWeights');
        $this->ensureOk($responses['noriWeights'] ?? null, 'nori.itemWeights');

        $wynnpoolWeights = $this->ensureJsonArray($responses['wynnpoolWeights'], 'wynnpool.itemWeights');
        $noriWeights     = $this->ensureJsonArray($responses['noriWeights'], 'nori.itemWeights');

        if (!isset($noriWeights['weights']) || !is_array($noriWeights['weights'])) {
            Log::warning('nori.itemWeights: missing or invalid "weights" key');
            throw new \RuntimeException('nori.itemWeights: invalid payload');
        }

        return [
            'wynnpool' => $this->transformWynnpoolWeights($wynnpoolWeights),
            'nori'     => $this->scaledWeights($noriWeights),
        ];
    }

    private function ensureOk(?Response $res, string $name): void
    {
        if (!$res || !$res->ok()) {
            $status = $res?->status();
            Log::warning("$name HTTP failure", ['status' => $status]);
            throw new \RuntimeException("$name fetch failed (status: {$status})");
        }
    }

    private function ensureJsonArray(Response $res, string $name): array
    {
        $json = $res->json();
        if (!is_array($json)) {
            Log::warning("$name: non-array JSON", ['body' => $res->body()]);
            throw new \RuntimeException("$name returned invalid JSON");
        }
        return $json;
    }

    private function transformWynnpoolWeights(array $weights): array
    {
        $out = [];
        foreach ($weights as $weight) {
            if (!isset($weight['item_name'], $weight['weight_name'], $weight['identifications'])) continue;
            $item = $weight['item_name'];
            $weightName = $weight['weight_name'];
            $out[$item][$weightName] = $weight['identifications'];
        }
        return $out;
    }

    private function scaledWeights(array $data): array
    {
        $weights = $data['weights'] ?? null;
        if (!is_array($weights)) {
            return [];
        }

        $out = $weights;
        array_walk_recursive($out, static function (&$v) {
            if (is_numeric($v)) {
                $v = $v / 100;
            }
        });

        return $out;
    }
}
