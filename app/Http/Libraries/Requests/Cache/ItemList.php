<?php

namespace App\Http\Libraries\Requests\Cache;

use App\Http\Libraries\ItemManager;
use App\Http\Libraries\Requests\WynnRequest;

class ItemList implements CacheContract
{

    public function refreshRate(): int
    {
        return 86400;
    }

    public function generate(): array
    {
        // TODO: itemsMap, converted, itemInfo, typeArray, material

        $wynnItems = WynnRequest::request()->get(config('athena.api.wynn.items'))->collect('items');
        if ($wynnItems === null) {
            return [];
        }


        $result = [];
        $items = [];
        $materialTypes = [];
        $translatedReferences = [];

        foreach ($wynnItems as $item) {
            $converted = ItemManager::convertItem($item);
            if ($converted['itemInfo'] !== null) {
                $itemInfo = $converted['itemInfo'];
                $typeArray = &$materialTypes[$itemInfo['type']];
                if (!is_array($typeArray)) {
                    $typeArray = [];
                }
                $material = $itemInfo['material'] ?? null;
                if ($material !== null && !in_array($material, $typeArray, true)) {
                    $typeArray[] = $material;
                }
            }

            if (array_key_exists('displayName', $item)) {
                $translatedReferences[$item['name']] = $item['displayName'];
            }

            $items[$item['name']] = $converted;
        }

        $wynnBuilderIDs = WynnRequest::request()->get(config('athena.api.wynn.builderIds'))->collect('items');
        if ($wynnBuilderIDs === null) {
            return [];
        }

        foreach ($wynnBuilderIDs as $wynnBuilderItem)
        {
            $item = &$items[$wynnBuilderItem['name']];
            $item['wynnBuilderID'] = $wynnBuilderItem['id'];
        }

        $result['items'] = $items;
        $result['materialTypes'] = $materialTypes;
        $result['translatedReferences'] = $translatedReferences;

        $result['identificationOrder'] = ItemManager::getIdentificationorder();
        $result['internalIdentifications'] = ItemManager::getInternalIdentifications();
        $result['majorIdentifications'] = ItemManager::getMajorIdentifications();

        return $result;
    }
}

