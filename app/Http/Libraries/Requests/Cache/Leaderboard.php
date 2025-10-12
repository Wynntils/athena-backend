<?php

namespace App\Http\Libraries\Requests\Cache;

use Http;
use Illuminate\Http\Client\Pool;
use Illuminate\Support\Facades\Cache;

class Leaderboard implements CacheContract
{
    public function refreshRate(): int
    {
        return 600;
    }

    public function generate(): array
    {
        $types = $this->getTypes();
        if (empty($types)) {
            throw new \Exception('No leaderboard types available');
        }

        $exclude = '4ca3e683-59f7-42b1-b727-b3e5d9da3ae6';
        $base = rtrim(config('athena.api.wynn.v3.leaderboards'), '/');

        $responses = Http::wynn()->pool(function (Pool $pool) use ($types, $base) {
            $reqs = [];
            foreach ($types as $type) {
                $reqs[] = $pool->as($type)->get("{$base}/{$type}", ['resultLimit' => 12]);
            }
            return $reqs;
        });

        $result = [];
        foreach ($responses as $type => $resp) {
            if (!$resp->successful()) {
                continue;
            }

            $entries = $resp->json();
            if (!is_array($entries)) {
                $entries = $resp->json();
            }
            if (!is_array($entries)) {
                continue;
            }

            $ids = collect($entries)
                ->map(fn(array $row) => $this->pickId($row, $type))
                ->filter();

            if (!$this->isGuildType($type)) {
                $ids = $ids->reject(fn(string $id) => $id === $exclude);
            }

            $result[$type] = $ids
                ->values()
                ->take(9)
                ->mapWithKeys(fn(string $id, int $i) => [(string)($i + 1) => $id])
                ->toArray();
        }

        return $result;
    }

    private function pickId(array $row, string $type): ?string
    {
        if ($this->isGuildType($type)) {
            return $row['guild']['name']
                ?? $row['guildName']
                ?? $row['name']
                ?? null;
        }

        return $row['characterUuid']
            ?? $row['uuid']
            ?? ($row['character']['uuid'] ?? null);
    }

    private function isGuildType(string $type): bool
    {
        return str_contains(strtolower($type), 'guild');
    }

    private function getTypes(): array
    {
        return Cache::remember('wynn.lb.types', now()->addHour(), function () {
            $base = rtrim(config('athena.api.wynn.v3.leaderboards'), '/');
            $resp = Http::wynn()->get("{$base}/types");
            if (!$resp->successful()) {
                throw new \Exception('Failed to fetch leaderboard types');
            }
            $types = $resp->json();
            return array_values(array_filter($types, 'is_string'));
        });
    }
}
