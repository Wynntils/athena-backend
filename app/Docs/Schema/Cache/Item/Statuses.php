<?php

namespace App\Docs\Schema\Cache\Item;

use OpenApi\Attributes as OA;

#[
    OA\Schema(
        title: 'Statuses',
        properties: [
            new OA\Property('rawStrength', '#/components/schemas/ItemStatus', nullable: true),
            new OA\Property('rawDexterity', '#/components/schemas/ItemStatus', nullable: true),
            new OA\Property('rawIntelligence', '#/components/schemas/ItemStatus', nullable: true),
            new OA\Property('rawDefence', '#/components/schemas/ItemStatus', nullable: true),
            new OA\Property('rawAgility', '#/components/schemas/ItemStatus', nullable: true),
            // second group {attack stuff}
            new OA\Property('attackSpeed', '#/components/schemas/ItemStatus', nullable: true),
            new OA\Property('rawMainAttackDamage', '#/components/schemas/ItemStatus', nullable: true),
            new OA\Property('mainAttackDamage', '#/components/schemas/ItemStatus', nullable: true),
            new OA\Property('rawNeutralMainAttackDamage', '#/components/schemas/ItemStatus', nullable: true),
            new OA\Property('neutralMainAttackDamage', '#/components/schemas/ItemStatus', nullable: true),
            new OA\Property('rawEarthMainAttackDamage', '#/components/schemas/ItemStatus', nullable: true),
            new OA\Property('earthMainAttackDamage', '#/components/schemas/ItemStatus', nullable: true),
            new OA\Property('rawThunderMainAttackDamage', '#/components/schemas/ItemStatus', nullable: true),
            new OA\Property('thunderMainAttackDamage', '#/components/schemas/ItemStatus', nullable: true),
            new OA\Property('rawWaterMainAttackDamage', '#/components/schemas/ItemStatus', nullable: true),
            new OA\Property('waterMainAttackDamage', '#/components/schemas/ItemStatus', nullable: true),
            new OA\Property('rawFireMainAttackDamage', '#/components/schemas/ItemStatus', nullable: true),
            new OA\Property('fireMainAttackDamage', '#/components/schemas/ItemStatus', nullable: true),
            new OA\Property('rawAirMainAttackDamage', '#/components/schemas/ItemStatus', nullable: true),
            new OA\Property('airMainAttackDamage', '#/components/schemas/ItemStatus', nullable: true),
            new OA\Property('rawElementalMainAttackDamage', '#/components/schemas/ItemStatus', nullable: true),
            new OA\Property('elementalMainAttackDamage', '#/components/schemas/ItemStatus', nullable: true),
            new OA\Property('rawSpellDamage', '#/components/schemas/ItemStatus', nullable: true),
            new OA\Property('spellDamage', '#/components/schemas/ItemStatus', nullable: true),
            new OA\Property('rawNeutralSpellDamage', '#/components/schemas/ItemStatus', nullable: true),
            new OA\Property('neutralSpellDamage', '#/components/schemas/ItemStatus', nullable: true),
            new OA\Property('rawEarthSpellDamage', '#/components/schemas/ItemStatus', nullable: true),
            new OA\Property('earthSpellDamage', '#/components/schemas/ItemStatus', nullable: true),
            new OA\Property('rawThunderSpellDamage', '#/components/schemas/ItemStatus', nullable: true),
            new OA\Property('thunderSpellDamage', '#/components/schemas/ItemStatus', nullable: true),
            new OA\Property('rawWaterSpellDamage', '#/components/schemas/ItemStatus', nullable: true),
            new OA\Property('waterSpellDamage', '#/components/schemas/ItemStatus', nullable: true),
            new OA\Property('rawFireSpellDamage', '#/components/schemas/ItemStatus', nullable: true),
            new OA\Property('fireSpellDamage', '#/components/schemas/ItemStatus', nullable: true),
            new OA\Property('rawAirSpellDamage', '#/components/schemas/ItemStatus', nullable: true),
            new OA\Property('airSpellDamage', '#/components/schemas/ItemStatus', nullable: true),
            new OA\Property('rawElementalSpellDamage', '#/components/schemas/ItemStatus', nullable: true),
            new OA\Property('elementalSpellDamage', '#/components/schemas/ItemStatus', nullable: true),
            // third group {health/mana stuff}
            new OA\Property('rawHealth', '#/components/schemas/ItemStatus', nullable: true),
            new OA\Property('rawHealthRegen', '#/components/schemas/ItemStatus', nullable: true),
            new OA\Property('healthRegen', '#/components/schemas/ItemStatus', nullable: true),
            new OA\Property('lifeSteal', '#/components/schemas/ItemStatus', nullable: true),
            new OA\Property('manaRegen', '#/components/schemas/ItemStatus', nullable: true),
            new OA\Property('manaSteal', '#/components/schemas/ItemStatus', nullable: true),
            // fourth group {damage stuff}
            new OA\Property('rawDamage', '#/components/schemas/ItemStatus', nullable: true),
            new OA\Property('damage', '#/components/schemas/ItemStatus', nullable: true),
            new OA\Property('rawNeutralDamage', '#/components/schemas/ItemStatus', nullable: true),
            new OA\Property('neutralDamage', '#/components/schemas/ItemStatus', nullable: true),
            new OA\Property('rawEarthDamage', '#/components/schemas/ItemStatus', nullable: true),
            new OA\Property('earthDamage', '#/components/schemas/ItemStatus', nullable: true),
            new OA\Property('rawThunderDamage', '#/components/schemas/ItemStatus', nullable: true),
            new OA\Property('thunderDamage', '#/components/schemas/ItemStatus', nullable: true),
            new OA\Property('rawWaterDamage', '#/components/schemas/ItemStatus', nullable: true),
            new OA\Property('waterDamage', '#/components/schemas/ItemStatus', nullable: true),
            new OA\Property('rawFireDamage', '#/components/schemas/ItemStatus', nullable: true),
            new OA\Property('fireDamage', '#/components/schemas/ItemStatus', nullable: true),
            new OA\Property('rawAirDamage', '#/components/schemas/ItemStatus', nullable: true),
            new OA\Property('airDamage', '#/components/schemas/ItemStatus', nullable: true),
            new OA\Property('rawElementalDamage', '#/components/schemas/ItemStatus', nullable: true),
            new OA\Property('elementalDamage', '#/components/schemas/ItemStatus', nullable: true),
            // fifth group {defence stuff}
            new OA\Property('earthDefence', '#/components/schemas/ItemStatus', nullable: true),
            new OA\Property('thunderDefence', '#/components/schemas/ItemStatus', nullable: true),
            new OA\Property('waterDefence', '#/components/schemas/ItemStatus', nullable: true),
            new OA\Property('fireDefence', '#/components/schemas/ItemStatus', nullable: true),
            new OA\Property('airDefence', '#/components/schemas/ItemStatus', nullable: true),
            // sixth group {passive damage}
            new OA\Property('exploding', '#/components/schemas/ItemStatus', nullable: true),
            new OA\Property('poison', '#/components/schemas/ItemStatus', nullable: true),
            new OA\Property('thorns', '#/components/schemas/ItemStatus', nullable: true),
            new OA\Property('reflection', '#/components/schemas/ItemStatus', nullable: true),
            // seventh group {movement stuff}
            new OA\Property('walkSpeed', '#/components/schemas/ItemStatus', nullable: true),
            new OA\Property('sprint', '#/components/schemas/ItemStatus', nullable: true),
            new OA\Property('sprintRegen', '#/components/schemas/ItemStatus', nullable: true),
            new OA\Property('rawJumpHeight', '#/components/schemas/ItemStatus', nullable: true),
            // eighth group {xp/gathering stuff}
            new OA\Property('soulPointRegen', '#/components/schemas/ItemStatus', nullable: true),
            new OA\Property('lootBonus', '#/components/schemas/ItemStatus', nullable: true),
            new OA\Property('lootQuality', '#/components/schemas/ItemStatus', nullable: true),
            new OA\Property('stealing', '#/components/schemas/ItemStatus', nullable: true),
            new OA\Property('xpBonus', '#/components/schemas/ItemStatus', nullable: true),
            new OA\Property('gatherXpBonus', '#/components/schemas/ItemStatus', nullable: true),
            new OA\Property('gatherSpeed', '#/components/schemas/ItemStatus', nullable: true),
            // ninth group {spell stuff}
            new OA\Property('raw1stSpellCost', '#/components/schemas/ItemStatus', nullable: true),
            new OA\Property('1stSpellCost', '#/components/schemas/ItemStatus', nullable: true),
            new OA\Property('raw2ndSpellCost', '#/components/schemas/ItemStatus', nullable: true),
            new OA\Property('2ndSpellCost', '#/components/schemas/ItemStatus', nullable: true),
            new OA\Property('raw3rdSpellCost', '#/components/schemas/ItemStatus', nullable: true),
            new OA\Property('3rdSpellCost', '#/components/schemas/ItemStatus', nullable: true),
            new OA\Property('raw4thSpellCost', '#/components/schemas/ItemStatus', nullable: true),
            new OA\Property('4thSpellCost', '#/components/schemas/ItemStatus', nullable: true)
        ],
        type: "object"
    ),
]
class Statuses
{

}
