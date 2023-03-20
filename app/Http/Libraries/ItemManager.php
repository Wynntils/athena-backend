<?php

namespace App\Http\Libraries;

use App\Enums\MajorIdentifications;
use App\Http\Traits\Singleton;

class ItemManager
{

    use Singleton;

    private \Illuminate\Support\Collection $itemDB;

    public function __construct()
    {
        $itemDB = \Storage::get('item-data.json');
        $this->itemDB = collect(json_decode($itemDB));
    }


    public static function convertItem(array $item): object
    {
        $input = collect($item);

        $result = $itemInfo = $requirements = $damageTypes = $defenseTypes = $statuses = [];

        $isFixed = static function ($raw) use ($input) {
            if ($input->get('identified') === true) {
                return true;
            }

            return match ($raw) {
                'rawStrength', 'rawDexterity', 'rawIntelligence', 'rawDefence', 'rawAgility' => true,
                default => false,
            };
        };

        $result['displayName'] = $input->get('displayName', str_replace('֎', '', $input['name']));
        $result['tier'] = strtoupper($input['tier']);
        $result['identified'] = $input->get('identified', false);
        $result['powderAmount'] = $input['sockets'];
        $result['attackSpeed'] = $input->get('attackSpeed');

        $result['itemInfo'] = &$itemInfo;
        $itemInfo['type'] = strtoupper($input->get('type', $input->get('accessoryType')));
        $itemInfo['set'] = $input['set'];
        $itemInfo['dropType'] = strtoupper($input['dropType']);
        $itemInfo['armorColor'] = $input->get('armorColor') === '160,101,64' ? null : $input->get('armorColor');

        $result['requirements'] = &$requirements;
        $requirements['quest'] = $input->get('quest');
        $requirements['classType'] = !empty($input->get('classRequirement')) ? strtoupper($input->get('classRequirement')) : null;
        $requirements['level'] = $input->get('level');
        $requirements['strength'] = $input->get('strength');
        $requirements['dexterity'] = $input->get('dexterity');
        $requirements['intelligence'] = $input->get('intelligence');
        $requirements['defense'] = $input->get('defense');
        $requirements['agility'] = $input->get('agility');

        $result['damageTypes'] = &$damageTypes;
        $damageTypes['neutral'] = ignoreZero($input->get('damage'));
        $damageTypes['earth'] = ignoreZero($input->get('earthDamage'));
        $damageTypes['thunder'] = ignoreZero($input->get('thunderDamage'));
        $damageTypes['water'] = ignoreZero($input->get('waterDamage'));
        $damageTypes['fire'] = ignoreZero($input->get('fireDamage'));
        $damageTypes['air'] = ignoreZero($input->get('airDamage'));

        $result['defenseTypes'] = &$defenseTypes;
        $defenseTypes['health'] = ignoreZero($input->get('health'));
        $defenseTypes['earth'] = ignoreZero($input->get('earthDefense'));
        $defenseTypes['thunder'] = ignoreZero($input->get('thunderDefense'));
        $defenseTypes['water'] = ignoreZero($input->get('waterDefense'));
        $defenseTypes['fire'] = ignoreZero($input->get('fireDefense'));
        $defenseTypes['air'] = ignoreZero($input->get('airDefense'));

        $result['statuses'] = &$statuses;

        $result['majorIds'] = $input->get('majorIds');
        $result['restriction'] = $input->get('restrictions');
        $result['lore'] = $input->get('addedLore');

        foreach ($item as $key => $value) {

            if ($key === 'armorType') {
                $itemInfo['material'] = str_replace('chain', 'chainmail', strtolower("minecraft:{$value}_{$itemInfo["type"]}"));
                continue;
            }

            if ($key === 'material' && $value !== null) {
                $itemInfo['material'] = $value;
                continue;
            }

            if (!is_numeric($value) || $value === 0) {
                continue;
            }

            $translatedName = self::translateStatusName($key);
            if ($translatedName === null) {
                continue;
            }
            $status = &$statuses[$translatedName];
            $status['type'] = getStatusType($translatedName);
            $status['isFixed'] = $isFixed($translatedName);
            $status['baseValue'] = $value;
        }

        // Enhance with new material IDs for Artemis
        if (isset($itemInfo['material'])) {
            self::enhanceWithNewMaterial($itemInfo['material'], $itemInfo);
        }

        $itemInfo = cleanNull($itemInfo);
        $requirements = cleanNull($requirements);
        $damageTypes = cleanNull($damageTypes);
        $defenseTypes = cleanNull($defenseTypes);
        $statuses = cleanNull($statuses);
        return cleanNull($result);
    }


    public static function getIdentificationOrder(): array
    {
        $result = [];

        // IMPORTANT! This order is used for the "chat encoding" protocol where an description
        // of an item is sent through the chat. This ordering must stay the same between
        // Athena (Legacy) and Artemis, or this chat encoding protocol will break between
        // the two mod versions.
        //
        // TL;DR: Changes in this list must be coordinated with Artemis.
        $result['order'] = collect([
            'rawStrength',
            'rawDexterity',
            'rawIntelligence',
            'rawDefence',
            'rawAgility',
            //second group {attack stuff}
            'attackSpeed',
            'rawMainAttackDamage',
            'mainAttackDamage',
            'rawNeutralMainAttackDamage',
            'neutralMainAttackDamage',
            'rawEarthMainAttackDamage',
            'earthMainAttackDamage',
            'rawThunderMainAttackDamage',
            'thunderMainAttackDamage',
            'rawWaterMainAttackDamage',
            'waterMainAttackDamage',
            'rawFireMainAttackDamage',
            'fireMainAttackDamage',
            'rawAirMainAttackDamage',
            'airMainAttackDamage',
            'rawElementalMainAttackDamage',
            'elementalMainAttackDamage',
            'rawSpellDamage',
            'spellDamage',
            'rawNeutralSpellDamage',
            'neutralSpellDamage',
            'rawEarthSpellDamage',
            'earthSpellDamage',
            'rawThunderSpellDamage',
            'thunderSpellDamage',
            'rawWaterSpellDamage',
            'waterSpellDamage',
            'rawFireSpellDamage',
            'fireSpellDamage',
            'rawAirSpellDamage',
            'airSpellDamage',
            'rawElementalSpellDamage',
            'elementalSpellDamage',
            //third group {health/mana stuff}
            'rawHealth',
            'rawHealthRegen',
            'healthRegen',
            'lifeSteal',
            'manaRegen',
            'manaSteal',
            //fourth group {damage stuff}
            'rawDamage',
            'damage',
            'rawNeutralDamage',
            'neutralDamage',
            'rawEarthDamage',
            'earthDamage',
            'rawThunderDamage',
            'thunderDamage',
            'rawWaterDamage',
            'waterDamage',
            'rawFireDamage',
            'fireDamage',
            'rawAirDamage',
            'airDamage',
            'rawElementalDamage',
            'elementalDamage',
            //fifth group {defence stuff}
            'earthDefence',
            'thunderDefence',
            'waterDefence',
            'fireDefence',
            'airDefence',
            //sixth group {passive damage}
            'exploding',
            'poison',
            'thorns',
            'reflection',
            //seventh group {movement stuff}
            'walkSpeed',
            'sprint',
            'sprintRegen',
            'rawJumpHeight',
            //eigth group {XP/Gathering stuff}
            'soulPointRegen',
            'lootBonus',
            'lootQuality',
            'stealing',
            'xpBonus',
            'gatherXpBonus',
            'gatherSpeed',
            //ninth group {spell stuff}
            'raw1stSpellCost',
            '1stSpellCost',
            'raw2ndSpellCost',
            '2ndSpellCost',
            'raw3rdSpellCost',
            '3rdSpellCost',
            'raw4thSpellCost',
            '4thSpellCost',
        ])->mapWithKeys(
            fn($value, $key) => [$value => $key + 1]
        )->toArray();

        $groups = &$result['groups'];

        $groups[] = '1-5';
        $groups[] = '6-38';
        $groups[] = '39-44';
        $groups[] = '45-60';
        $groups[] = '61-65';
        $groups[] = '66-69';
        $groups[] = '70-73';
        $groups[] = '74-80';
        $groups[] = '81-88';

        $inverted = &$result['inverted'];

        $inverted[] = '1stSpellCost';
        $inverted[] = '2ndSpellCost';
        $inverted[] = '3rdSpellCost';
        $inverted[] = '4thSpellCost';
        $inverted[] = 'raw1stSpellCost';
        $inverted[] = 'raw2ndSpellCost';
        $inverted[] = 'raw3rdSpellCost';
        $inverted[] = 'raw4thSpellCost';


        return $result;
    }

    public static function getInternalIdentifications(): array
    {
        $result = [];

        $result['STRENGTHPOINTS'] = 'rawStrength';
        $result['DEXTERITYPOINTS'] = 'rawDexterity';
        $result['INTELLIGENCEPOINTS'] = 'rawIntelligence';
        $result['DEFENSEPOINTS'] = 'rawDefence';
        $result['AGILITYPOINTS'] = 'rawAgility';
        // second group {attack stuff}
        $result['ATTACKSPEED'] = 'attackSpeed';
        $result['DAMAGEBONUSRAW'] = 'rawMainAttackDamage';
        $result['DAMAGEBONUS'] = 'mainAttackDamage';
        $result['MAIN_ATTACK_NEUTRAL_DAMAGE_BONUS_RAW'] = 'rawNeutralMainAttackDamage';
        $result['MAIN_ATTACK_NEUTRAL_DAMAGE_BONUS'] = 'neutralMainAttackDamage';
        $result['MAIN_ATTACK_EARTH_DAMAGE_BONUS_RAW'] = 'rawEarthMainAttackDamage';
        $result['MAIN_ATTACK_EARTH_DAMAGE_BONUS'] = 'earthMainAttackDamage';
        $result['MAIN_ATTACK_THUNDER_DAMAGE_BONUS_RAW'] = 'rawThunderMainAttackDamage';
        $result['MAIN_ATTACK_THUNDER_DAMAGE_BONUS'] = 'thunderMainAttackDamage';
        $result['MAIN_ATTACK_WATER_DAMAGE_BONUS_RAW'] = 'rawWaterMainAttackDamage';
        $result['MAIN_ATTACK_WATER_DAMAGE_BONUS'] = 'waterMainAttackDamage';
        $result['MAIN_ATTACK_FIRE_DAMAGE_BONUS_RAW'] = 'rawFireMainAttackDamage';
        $result['MAIN_ATTACK_FIRE_DAMAGE_BONUS'] = 'fireMainAttackDamage';
        $result['MAIN_ATTACK_AIR_DAMAGE_BONUS_RAW'] = 'rawAirMainAttackDamage';
        $result['MAIN_ATTACK_AIR_DAMAGE_BONUS'] = 'airMainAttackDamage';
        $result['MAIN_ATTACK_ELEMENTAL_DAMAGE_BONUS_RAW'] = 'rawElementalMainAttackDamage';
        $result['MAIN_ATTACK_ELEMENTAL_DAMAGE_BONUS'] = 'elementalMainAttackDamage';
        $result['SPELLDAMAGERAW'] = 'rawSpellDamage';
        $result['SPELLDAMAGE'] = 'spellDamage';
        $result['SPELL_NEUTRAL_DAMAGE_BONUS_RAW'] = 'rawNeutralSpellDamage';
        $result['SPELL_NEUTRAL_DAMAGE_BONUS'] = 'neutralSpellDamage';
        $result['SPELL_EARTH_DAMAGE_BONUS_RAW'] = 'rawEarthSpellDamage';
        $result['SPELL_EARTH_DAMAGE_BONUS'] = 'earthSpellDamage';
        $result['SPELL_THUNDER_DAMAGE_BONUS_RAW'] = 'rawThunderSpellDamage';
        $result['SPELL_THUNDER_DAMAGE_BONUS'] = 'thunderSpellDamage';
        $result['SPELL_WATER_DAMAGE_BONUS_RAW'] = 'rawWaterSpellDamage';
        $result['SPELL_WATER_DAMAGE_BONUS'] = 'waterSpellDamage';
        $result['SPELL_FIRE_DAMAGE_BONUS_RAW'] = 'rawFireSpellDamage';
        $result['SPELL_FIRE_DAMAGE_BONUS'] = 'fireSpellDamage';
        $result['SPELL_AIR_DAMAGE_BONUS_RAW'] = 'rawAirSpellDamage';
        $result['SPELL_AIR_DAMAGE_BONUS'] = 'airSpellDamage';
        $result['RAINBOWSPELLDAMAGERAW'] = 'rawElementalSpellDamage';
        $result['SPELL_ELEMENTAL_DAMAGE_BONUS'] = 'elementalSpellDamage';
        // third group {health/mana stuff}
        $result['HEALTHBONUS'] = 'rawHealth';
        $result['HEALTHREGENRAW'] = 'rawHealthRegen';
        $result['HEALTHREGEN'] = 'healthRegen';
        $result['LIFESTEAL'] = 'lifeSteal';
        $result['MANAREGEN'] = 'manaRegen';
        $result['MANASTEAL'] = 'manaSteal';
        // fourth group {damage stuff}
        $result['DAMAGE_BONUS_RAW'] = 'rawDamage';
        $result['DAMAGE_BONUS'] = 'damage';
        $result['NEUTRAL_DAMAGE_BONUS_RAW'] = 'rawNeutralDamage';
        $result['NEUTRAL_DAMAGE_BONUS'] = 'neutralDamage';
        $result['EARTH_DAMAGE_BONUS_RAW'] = 'rawEarthDamage';
        $result['EARTHDAMAGEBONUS'] = 'earthDamage';
        $result['THUNDER_DAMAGE_BONUS_RAW'] = 'rawThunderDamage';
        $result['THUNDERDAMAGEBONUS'] = 'thunderDamage';
        $result['WATER_DAMAGE_BONUS_RAW'] = 'rawWaterDamage';
        $result['WATERDAMAGEBONUS'] = 'waterDamage';
        $result['FIRE_DAMAGE_BONUS_RAW'] = 'rawFireDamage';
        $result['FIREDAMAGEBONUS'] = 'fireDamage';
        $result['AIR_DAMAGE_BONUS_RAW'] = 'rawAirDamage';
        $result['AIRDAMAGEBONUS'] = 'airDamage';
        $result['ELEMENTAL_DAMAGE_BONUS_RAW'] = 'rawElementalDamage';
        $result['ELEMENTAL_DAMAGE_BONUS'] = 'elementalDamage';
        // fifth group {defence stuff}
        $result['EARTHDEFENSE'] = 'earthDefence';
        $result['THUNDERDEFENSE'] = 'thunderDefence';
        $result['WATERDEFENSE'] = 'waterDefence';
        $result['FIREDEFENSE'] = 'fireDefence';
        $result['AIRDEFENSE'] = 'airDefence';
        // sixth group {passive damage}
        $result['EXPLODING'] = 'exploding';
        $result['POISON'] = 'poison';
        $result['THORNS'] = 'thorns';
        $result['REFLECTION'] = 'reflection';
        // seventh group {movement stuff}
        $result['SPEED'] = 'walkSpeed';
        $result['STAMINA'] = 'sprint';
        $result['STAMINA_REGEN'] = 'sprintRegen';
        $result['JUMP_HEIGHT'] = 'rawJumpHeight';
        // eighth group {xp/gathering stuff}
        $result['SOULPOINTS'] = 'soulPointRegen';
        $result['LOOTBONUS'] = 'lootBonus';
          // lootQuality is not here because it only exists on crafted items
        $result['EMERALDSTEALING'] = 'stealing';
        $result['XPBONUS'] = 'xpBonus';
          // gatherXpBonus is not here because it only exists on crafted items
          // gatherSpeed is not here because it only exists on crafted items
        // ninth group {spell stuff}
        $result['SPELL_COST_RAW_1'] = 'raw1stSpellCost';
        $result['SPELL_COST_PCT_1'] = '1stSpellCost';
        $result['SPELL_COST_RAW_2'] = 'raw2ndSpellCost';
        $result['SPELL_COST_PCT_2'] = '2ndSpellCost';
        $result['SPELL_COST_RAW_3'] = 'raw3rdSpellCost';
        $result['SPELL_COST_PCT_3'] = '3rdSpellCost';
        $result['SPELL_COST_RAW_4'] = 'raw4thSpellCost';
        $result['SPELL_COST_PCT_4'] = '4thSpellCost';

        return $result;
    }

    public static function getMajorIdentifications(): array {
        $result = [];

        foreach (MajorIdentifications::cases() as $enum) {
            $result[$enum->name] = [
                'name' => $enum->displayName(),
                'description' => $enum->description()
            ];
        }

        return $result;
    }

    private static function translateStatusName(string $raw): ?string
    {
        return match ($raw) {
            'strengthPoints' => 'rawStrength',
            'dexterityPoints' => 'rawDexterity',
            'intelligencePoints' => 'rawIntelligence',
            'defensePoints' => 'rawDefence',
            'agilityPoints' => 'rawAgility',
            // second group {attack stuff}
            'attackSpeedBonus' => 'attackSpeed',
            'mainAttackDamageBonusRaw' => 'rawMainAttackDamage',
            'mainAttackDamageBonus' => 'mainAttackDamage',
            'mainAttackNeutralDamageBonusRaw' => 'rawMainAttackNeutralDamage',
            'mainAttackNeutralDamageBonus' => 'mainAttackNeutralDamage',
            'mainAttackEarthDamageBonusRaw' => 'rawMainAttackEarthDamage',
            'mainAttackEarthDamageBonus' => 'mainAttackEarthDamage',
            'mainAttackThunderDamageBonusRaw' => 'rawMainAttackThunderDamage',
            'mainAttackThunderDamageBonus' => 'mainAttackThunderDamage',
            'mainAttackWaterDamageBonusRaw' => 'rawMainAttackWaterDamage',
            'mainAttackWaterDamageBonus' => 'mainAttackWaterDamage',
            'mainAttackFireDamageBonusRaw' => 'rawMainAttackFireDamage',
            'mainAttackFireDamageBonus' => 'mainAttackFireDamage',
            'mainAttackAirDamageBonusRaw' => 'rawMainAttackAirDamage',
            'mainAttackAirDamageBonus' => 'mainAttackAirDamage',
            'mainAttackElementalDamageBonusRaw' => 'rawMainAttackElementalDamage',
            'mainAttackElementalDamageBonus' => 'mainAttackElementalDamage',
            'spellDamageBonusRaw' => 'rawSpellDamage',
            'spellDamageBonus' => 'spellDamage',
            'spellNeutralDamageBonusRaw' => 'rawSpellNeutralDamage',
            'spellNeutralDamageBonus' => 'spellNeutralDamage',
            'spellEarthDamageBonusRaw' => 'rawSpellEarthDamage',
            'spellEarthDamageBonus' => 'spellEarthDamage',
            'spellThunderDamageBonusRaw' => 'rawSpellThunderDamage',
            'spellThunderDamageBonus' => 'spellThunderDamage',
            'spellWaterDamageBonusRaw' => 'rawSpellWaterDamage',
            'spellWaterDamageBonus' => 'spellWaterDamage',
            'spellFireDamageBonusRaw' => 'rawSpellFireDamage',
            'spellFireDamageBonus' => 'spellFireDamage',
            'spellAirDamageBonusRaw' => 'rawSpellAirDamage',
            'spellAirDamageBonus' => 'spellAirDamage',
            'spellElementalDamageBonusRaw' => 'rawSpellElementalDamage',
            'spellElementalDamageBonus' => 'spellElementalDamage',
            // third group {health/mana stuff}
            'healthBonus' => 'rawHealth',
            'healthRegenRaw' => 'rawHealthRegen',
            'healthRegen' => 'healthRegen',
            'lifeSteal' => 'lifeSteal',
            'manaRegen' => 'manaRegen',
            'manaSteal' => 'manaSteal',
            // fourth group {damage stuff}
            'damageBonusRaw' => 'rawDamage',
            'damageBonus' => 'damage',
            'neutralDamageBonusRaw' => 'rawNeutralDamage',
            'neutralDamageBonus' => 'neutralDamage',
            'earthDamageBonusRaw' => 'rawEarthDamage',
            'earthDamageBonus' => 'earthDamage',
            'thunderDamageBonusRaw' => 'rawThunderDamage',
            'thunderDamageBonus' => 'thunderDamage',
            'waterDamageBonusRaw' => 'rawWaterDamage',
            'waterDamageBonus' => 'waterDamage',
            'fireDamageBonusRaw' => 'rawFireDamage',
            'fireDamageBonus' => 'fireDamage',
            'airDamageBonusRaw' => 'rawAirDamage',
            'airDamageBonus' => 'airDamage',
            'elementalDamageBonusRaw' => 'rawElementalDamage',
            'elementalDamageBonus' => 'elementalDamage',
            // fifth group {defence stuff}
            'bonusEarthDefense', 'earthDefenseBonus' => 'earthDefence',
            'bonusThunderDefense', 'thunderDefenseBonus' => 'thunderDefence',
            'bonusWaterDefense', 'waterDefenseBonus' => 'waterDefence',
            'bonusFireDefense', 'fireDefenseBonus' => 'fireDefence',
            'bonusAirDefense', 'airDefenseBonus' => 'airDefence',
            // sixth group {passive damage}
            'exploding' => 'exploding',
            'poison' => 'poison',
            'thorns' => 'thorns',
            'reflection' => 'reflection',
            // seventh group {movement stuff}
            'speed' => 'walkSpeed',
            'sprint' => 'sprint',
            'sprintRegen' => 'sprintRegen',
            'jumpHeight' => 'rawJumpHeight',
            // eighth group {xp/gathering stuff}
            'soulPoints' => 'soulPointRegen',
            'lootBonus' => 'lootBonus',
            'lootQuality' => 'lootQuality',
            'emeraldStealing' => 'stealing',
            'xpBonus' => 'xpBonus',
            'gatherXpBonus' => 'gatherXpBonus',
            'gatherSpeed' => 'gatherSpeed',
            // ninth group {spell stuff}
            'spellCostRaw1' => 'raw1stSpellCost',
            'spellCostPct1', 'spellCost1Pct' => '1stSpellCost',
            'spellCostRaw2' => 'raw2ndSpellCost',
            'spellCostPct2', 'spellCost2Pct' => '2ndSpellCost',
            'spellCostRaw3' => 'raw3rdSpellCost',
            'spellCostPct3', 'spellCost3Pct' => '3rdSpellCost',
            'spellCostRaw4' => 'raw4thSpellCost',
            'spellCostPct4', 'spellCost4Pct' => '4thSpellCost',

            default => null,
        };
    }

    public static function enhanceWithNewMaterial($material, &$itemInfo) {
        try {
            $newMaterial = self::instance()->convertMaterial($material);
            $itemInfo['name'] = $newMaterial['name'];
            $itemInfo['damage'] = $newMaterial['damage'];
        } catch (\Exception $e) {
            $itemInfo['materialException'] = $e->getMessage();
        }
    }

    public function convertMaterial($material) {
        if (str($material)->startsWith('minecraft:')) {
            return [
                'name' => $material,
                'damage' => null
            ];
        }

        $item = $this->itemDB->get($material);
        if ($item === null) {
            $lookup = explode(':', $material);
            $item = $this->itemDB->get($lookup[0]);
        }

        if ($item === null) {
            throw new \Exception('Unknown material: ' . $material);
        }

        return [
            'name' => 'minecraft:' . $item?->name ?? 'unknown',
            'damage' => $lookup[1] ?? null,
        ];
    }

    public function getItemTranslations($materials)
    {
        $materials = collect($materials)->flatten()->unique();

        return $materials->mapWithKeys(function ($item, $key) {
            return [$item => $this->convertMaterial($item)];
        });
    }
}
