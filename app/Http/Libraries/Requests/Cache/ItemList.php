<?php

namespace App\Http\Libraries\Requests\Cache;

use App\Http\Libraries\ItemManager;
use GuzzleHttp\Exception\ConnectException;
use Http;
use Illuminate\Http\Client\Pool;


class ItemList implements CacheContract
{

    public function refreshRate(): int
    {
        return 86400;
    }

    public function generate(): array
    {
        /** @var \Illuminate\Http\Client\Response[] $responses */
        $responses = Http::wynn()->pool(fn (Pool $pool) => [
            $pool->as('wynnItems')->get(config('athena.api.wynn.v3.items')),
            $pool->as('wynnBuilderIDs')->get(config('athena.api.wynn.builderIds'))
        ]);

        // Check if the pool failed to fetch any of the requests
        if (array_filter($responses, static fn ($response) => $response instanceof ConnectException)) {
            throw new \Exception('Failed to fetch items from Wynn API');
        }

        $wynnItems = $responses['wynnItems']->json();
        if ($wynnItems === null) {
            throw new \Exception('Failed to fetch items from Wynn API');
        }

        $result = $items = $materialTypes = $translatedReferences = [];

        foreach ($wynnItems as $itemName => $item) {
            // Quick fix for v3 api
            $item['name'] = $itemName;
            $converted = ItemManager::convertItem($item);
            if ($converted->itemInfo !== null) {
                $itemInfo = $converted->itemInfo;
                $typeArray = &$materialTypes[$itemInfo->type];
                if (!is_array($typeArray)) {
                    $typeArray = [];
                }
                $material = $itemInfo->material ?? null;
                if ($material !== null && !in_array($material, $typeArray, true)) {
                    $typeArray[] = $material;
                }
            }

            if (array_key_exists('displayName', $item)) {
                $translatedReferences[$item['name']] = $item['displayName'];
            }

            $items[$item['name']] = $converted;
        }

        $wynnBuilderIDs = $responses['wynnBuilderIDs']->collect('items');
        if ($wynnBuilderIDs === null) {
            return [];
        }

        foreach ($wynnBuilderIDs as $wynnBuilderItem) {
            if (!isset($items[$wynnBuilderItem['name']])) {
                continue;
            }

            $item = &$items[$wynnBuilderItem['name']];
            $item->wynnBuilderID = $wynnBuilderItem['id'];
        }

        $result['items'] = array_values(array_filter($items, static fn($value) => !is_null($value) ));
        $result['materialTypes'] = $materialTypes;
        $result['translatedReferences'] = $translatedReferences;

        $result['identificationOrder'] = ItemManager::getIdentificationOrder();
        $result['internalIdentifications'] = ItemManager::getInternalIdentifications();
        $result['majorIdentifications'] = ItemManager::getMajorIdentifications();

        return $result;
    }
}
