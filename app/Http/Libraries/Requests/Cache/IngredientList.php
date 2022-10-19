<?php

namespace App\Http\Libraries\Requests\Cache;

use App\Http\Libraries\IngredientManager;
use Http;

class IngredientList implements CacheContract
{

    public function refreshRate(): int
    {
        return 86400;
    }

    public function generate(): array
    {
        $wynnIngredients = Http::wynn()->get(config('athena.api.wynn.ingredients'))->collect('data');
        if ($wynnIngredients === null) {
            throw new \Exception('Failed to fetch ingredients from Wynn API');
        }

        return [
            'ingredients' => $wynnIngredients->map(static function ($ingredient) {
                return IngredientManager::convertIngredient($ingredient);
            }),
            'headTextures' => IngredientManager::getHeadTextures()
        ];
    }
}
