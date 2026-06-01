<?php

namespace App\Http\Resources\Cache;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class HashesResource extends JsonResource
{
    public static $wrap = null;

    public function toArray(Request $request): array
    {
        return [
            /**
             * SHA-512 hash of the current `guildList` cache payload. Null when the cache is not populated.
             *
             * @example "6b3f37910e3a2dba223befc06d99febccc3fca617d0c21a7c09b84e522a5742ff0ac87f61dae22d3526845625206b799e0e68ad3db4befe553530144e2e60417"
             */
            'guildList' => $this->resource['guildList'] ?? null,
            /**
             * SHA-512 hash of the current `serverList` cache payload. Null when the cache is not populated.
             *
             * @example "3dc30156bd08062ace57acc48ce70362ff187833121e0c8198f2b8c668d4ab94ff434a7f0fba0c2ff69514204c7a3757c535fc2a175b234f5e4af9bd1bbb6893"
             */
            'serverList' => $this->resource['serverList'] ?? null,
            /**
             * SHA-512 hash of the current `itemWeights` cache payload. Null when the cache is not populated.
             *
             * @example "d19ed90c5e540fc0e310d47b6f9ddcb6bb58c3b5213c83c7be9d5fcd89d89657034a29fd2b7d733a965c6f36c7663c1066df6921b97e61960fb95f098644ed2c"
             */
            'itemWeights' => $this->resource['itemWeights'] ?? null,
            /**
             * SHA-512 hash of the current `leaderboard` cache payload. Null when the cache is not populated.
             *
             * @example "db54dcba98e5bc6f50ba5a0013eb97e1433f20fb6f24ba0d6bc486e5b128414389be9f3f5923308262b8779f6cb9d7233f46b3c37087a6c290a8283c330765a6"
             */
            'leaderboard' => $this->resource['leaderboard'] ?? null,
            /**
             * SHA-512 hash of the current `territoryList` cache payload. Null when the cache is not populated.
             *
             * @example "85ffbf993a1f0b11780d1c9ceb1ed2fb4b0ee7c2daf61334dad5ce3b7dd183ce19ab78e21634d71667970b3184f254136921b8373e7f5cacb56e0f8667baecb8"
             */
            'territoryList' => $this->resource['territoryList'] ?? null,
            /**
             * SHA-512 hash of the current `worldEvents` cache payload. Null when the cache is not populated.
             *
             * @example "09d96f6eb551702474425ce082c491b4fcde6bbc2fd043f7ffb6c39755b3a113808855c780528d8e9b0ab284ec119b6d19d8a400f8606a93e9b5bd1c886c901d"
             */
            'worldEvents' => $this->resource['worldEvents'] ?? null,
            /**
             * SHA-512 hash of the current `lootPools` cache payload. Null when the cache is not populated.
             *
             * @example "7c571cb89ccd6cac5f8730a4373aa39e8009f94d63bb482eb0ecd116a7e17e601fe111dcf92233d8d12134f006f8c5f56a766a0f89c07ae341bc36d6ee890914"
             */
            'lootPools' => $this->resource['lootPools'] ?? null,
        ];
    }
}
