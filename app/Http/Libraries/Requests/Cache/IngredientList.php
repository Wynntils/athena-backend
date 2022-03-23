<?php

namespace App\Http\Libraries\Requests\Cache;

use App\Http\Libraries\IngredientManager;
use App\Http\Libraries\Requests\WynnRequest;

class IngredientList implements CacheContract
{

    public function refreshRate(): int
    {
        return 86400;
    }

    public function generate(): array
    {
        $wynnIngredients = WynnRequest::request()->get(config('athena.api.wynn.ingredients'))->collect('data');
        if ($wynnIngredients === null) {
            return [];
        }

        return [
            'ingredients' => $wynnIngredients->map(static function ($ingredient) {
                return IngredientManager::convertIngredient($ingredient);
            }),
            'headTextures' => IngredientManager::getHeadTextures()
        ];
    }
}

