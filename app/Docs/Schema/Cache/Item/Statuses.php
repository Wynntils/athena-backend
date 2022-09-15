<?php

namespace App\Docs\Schema\Cache\Item;

use OpenApi\Attributes as OA;

#[
    OA\Schema(
        title: 'Statuses',
        properties: [
            new OA\Property(
                property: '1stSpellCost',
                ref: '#/components/schemas/ItemStatus',
                nullable: true,
            ),
            new OA\Property(
                property: '2ndSpellCost',
                ref: '#/components/schemas/ItemStatus',
                nullable: true,
            ),
            new OA\Property(
                property: '3rdSpellCost',
                ref: '#/components/schemas/ItemStatus',
                nullable: true,
            ),
            new OA\Property(
                property: '4thSpellCost',
                ref: '#/components/schemas/ItemStatus',
                nullable: true,
            ),
            new OA\Property(
                property: 'raw1stSpellCost',
                ref: '#/components/schemas/ItemStatus',
                nullable: true,
            ),
            new OA\Property(
                property: 'raw2ndSpellCost',
                ref: '#/components/schemas/ItemStatus',
                nullable: true,
            ),
            new OA\Property(
                property: 'raw3rdSpellCost',
                ref: '#/components/schemas/ItemStatus',
                nullable: true,
            ),
            new OA\Property(
                property: 'raw4thSpellCost',
                ref: '#/components/schemas/ItemStatus',
                nullable: true,
            ),
            new OA\Property(
                property: 'rawSpellDamage',
                ref: '#/components/schemas/ItemStatus',
                nullable: true,
            ),
            new OA\Property(
                property: 'rawMainAttackDamage',
                ref: '#/components/schemas/ItemStatus',
                nullable: true,
            ),
            new OA\Property(
                property: 'mainAttackDamage',
                ref: '#/components/schemas/ItemStatus',
                nullable: true,
            ),
            new OA\Property(
                property: 'rawHealthRegen',
                ref: '#/components/schemas/ItemStatus',
                nullable: true,
            ),
            new OA\Property(
                property: 'rawHealth',
                ref: '#/components/schemas/ItemStatus',
                nullable: true,
            ),
            new OA\Property(
                property: 'walkSpeed',
                ref: '#/components/schemas/ItemStatus',
                nullable: true,
            ),
            new OA\Property(
                property: 'soulPointRegen',
                ref: '#/components/schemas/ItemStatus',
                nullable: true,
            ),
            new OA\Property(
                property: 'stealing',
                ref: '#/components/schemas/ItemStatus',
                nullable: true,
            ),
            new OA\Property(
                property: 'rawStrength',
                ref: '#/components/schemas/ItemStatus',
                nullable: true,
            ),
            new OA\Property(
                property: 'rawDexterity',
                ref: '#/components/schemas/ItemStatus',
                nullable: true,
            ),
            new OA\Property(
                property: 'rawIntelligence',
                ref: '#/components/schemas/ItemStatus',
                nullable: true,
            ),
            new OA\Property(
                property: 'rawDefence',
                ref: '#/components/schemas/ItemStatus',
                nullable: true,
            ),
            new OA\Property(
                property: 'rawAgility',
                ref: '#/components/schemas/ItemStatus',
                nullable: true,
            ),
            new OA\Property(
                property: 'earthDamage',
                ref: '#/components/schemas/ItemStatus',
                nullable: true,
            ),
            new OA\Property(
                property: 'thunderDamage',
                ref: '#/components/schemas/ItemStatus',
                nullable: true,
            ),
            new OA\Property(
                property: 'waterDamage',
                ref: '#/components/schemas/ItemStatus',
                nullable: true,
            ),
            new OA\Property(
                property: 'fireDamage',
                ref: '#/components/schemas/ItemStatus',
                nullable: true,
            ),
            new OA\Property(
                property: 'airDamage',
                ref: '#/components/schemas/ItemStatus',
                nullable: true,
            ),
            new OA\Property(
                property: 'earthDefence',
                ref: '#/components/schemas/ItemStatus',
                nullable: true,
            ),
            new OA\Property(
                property: 'thunderDefence',
                ref: '#/components/schemas/ItemStatus',
                nullable: true,
            ),
            new OA\Property(
                property: 'waterDefence',
                ref: '#/components/schemas/ItemStatus',
                nullable: true,
            ),
            new OA\Property(
                property: 'fireDefence',
                ref: '#/components/schemas/ItemStatus',
                nullable: true,
            ),
            new OA\Property(
                property: 'airDefence',
                ref: '#/components/schemas/ItemStatus',
                nullable: true,
            ),
            new OA\Property(
                property: 'rawJumpHeight',
                ref: '#/components/schemas/ItemStatus',
                nullable: true,
            ),
            new OA\Property(
                property: 'rawSpellDamage',
                ref: '#/components/schemas/ItemStatus',
                nullable: true,
            ),
            new OA\Property(
                property: 'gatherXPBonus',
                ref: '#/components/schemas/ItemStatus',
                nullable: true,
            ),
            new OA\Property(
                property: 'attackSpeed',
                ref: '#/components/schemas/ItemStatus',
                nullable: true,
            ),
            new OA\Property(
                property: 'spellDamage',
                ref: '#/components/schemas/ItemStatus',
                nullable: true,
            ),
            new OA\Property(
                property: 'healthRegen',
                ref: '#/components/schemas/ItemStatus',
                nullable: true,
            ),
            new OA\Property(
                property: 'poison',
                ref: '#/components/schemas/ItemStatus',
                nullable: true,
            ),
            new OA\Property(
                property: 'lifeSteal',
                ref: '#/components/schemas/ItemStatus',
                nullable: true,
            ),
            new OA\Property(
                property: 'manaRegen',
                ref: '#/components/schemas/ItemStatus',
                nullable: true,
            ),
            new OA\Property(
                property: 'exploding',
                ref: '#/components/schemas/ItemStatus',
                nullable: true,
            ),
            new OA\Property(
                property: 'sprint',
                ref: '#/components/schemas/ItemStatus',
                nullable: true,
            ),
            new OA\Property(
                property: 'sprintRegen',
                ref: '#/components/schemas/ItemStatus',
                nullable: true,
            ),
            new OA\Property(
                property: 'lootBonus',
                ref: '#/components/schemas/ItemStatus',
                nullable: true,
            ),
            new OA\Property(
                property: 'lootQuality',
                ref: '#/components/schemas/ItemStatus',
                nullable: true,
            ),
            new OA\Property(
                property: 'gatherSpeed',
                ref: '#/components/schemas/ItemStatus',
                nullable: true,
            ),
            new OA\Property(
                property: 'xpBonus',
                ref: '#/components/schemas/ItemStatus',
                nullable: true,
            ),
            new OA\Property(
                property: 'manaSteal',
                ref: '#/components/schemas/ItemStatus',
                nullable: true,
            ),
            new OA\Property(
                property: 'thorns',
                ref: '#/components/schemas/ItemStatus',
                nullable: true,
            ),
            new OA\Property(
                property: 'reflection',
                ref: '#/components/schemas/ItemStatus',
                nullable: true,
            ),
        ],
        type: "object"
    ),
]
class Statuses
{

}
