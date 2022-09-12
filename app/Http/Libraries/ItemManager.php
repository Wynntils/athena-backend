<?php

namespace App\Http\Libraries;

use App\Http\Enums\MajorIdentifications;

class ItemManager
{

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

        $itemInfo = cleanNull($itemInfo);
        $requirements = cleanNull($requirements);
        $damageTypes = cleanNull($damageTypes);
        $defenseTypes = cleanNull($defenseTypes);
        $statuses = cleanNull($statuses);
        return cleanNull($result);
    }


    public static function getIdentificationorder(): array
    {
        $result = [];

        $order = &$result['order'];

        $order['rawStrength'] = 1;
        $order['rawDexterity'] = 2;
        $order['rawIntelligence'] = 3;
        $order['rawDefence'] = 4;
        $order['rawAgility'] = 5;
        //second group {attack stuff}
        $order['attackSpeed'] = 6;
        $order['rawMainAttackNeutralDamage'] = 7;
        $order['mainAttackDamage'] = 8;
        $order['rawNeutralSpellDamage'] = 9;
        $order['rawSpellDamage'] = 10;
        $order['spellDamage'] = 11;
        $order['rawThunderSpellDamage'] = 12;
        $order['rawFireSpellDamage'] = 13;
        $order['rawAirSpellDamage'] = 14;
        $order['rawEarthSpellDamage'] = 15;
        $order['rawWaterSpellDamage'] = 16;
        //third group {health/mana stuff}
        $order['rawHealth'] = 17;
        $order['rawHealthRegen'] = 18;
        $order['healthRegen'] = 19;
        $order['lifeSteal'] = 20;
        $order['manaRegen'] = 21;
        $order['manaSteal'] = 22;
        //fourth group {damage stuff}
        $order['earthDamage'] = 23;
        $order['thunderDamage'] = 24;
        $order['waterDamage'] = 25;
        $order['fireDamage'] = 26;
        $order['airDamage'] = 27;
        //fifth group {defence stuff}
        $order['earthDefence'] = 28;
        $order['thunderDefence'] = 29;
        $order['waterDefence'] = 30;
        $order['fireDefence'] = 31;
        $order['airDefence'] = 32;
        //sixth group {passive damage}
        $order['exploding'] = 33;
        $order['poison'] = 34;
        $order['thorns'] = 35;
        $order['reflection'] = 36;
        //seventh group {movement stuff}
        $order['walkSpeed'] = 37;
        $order['sprint'] = 38;
        $order['sprintRegen'] = 39;
        $order['rawJumpHeight'] = 40;
        //eigth group {XP/Gathering stuff}
        $order['soulPointRegen'] = 41;
        $order['lootBonus'] = 42;
        $order['lootQuality'] = 43;
        $order['stealing'] = 44;
        $order['xpBonus'] = 45;
        $order['gatherXpBonus'] = 46;
        $order['gatherSpeed'] = 47;
        //ninth group {spell stuff}
        $order['raw1stSpellCost'] = 48;
        $order['1stSpellCost'] = 49;
        $order['raw2ndSpellCost'] = 50;
        $order['2ndSpellCost'] = 51;
        $order['raw3rdSpellCost'] = 52;
        $order['3rdSpellCost'] = 53;
        $order['raw4thSpellCost'] = 54;
        $order['4thSpellCost'] = 55;

        $groups = &$result['groups'];

        $groups[] = '1-5';
        $groups[] = '6-11';
        $groups[] = '12-17';
        $groups[] = '18-22';
        $groups[] = '23-27';
        $groups[] = '28-31';
        $groups[] = '32-35';
        $groups[] = '36-42';
        $groups[] = '43-50';

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
        $result['MAINATTACKDAMAGEBONUS'] = 'mainAttackDamage';
        $result['MAINATTACKDAMAGEBONUSRAW'] = 'rawMainAttackNeutralDamage';
        $result['SPELLDAMAGEBONUS'] = 'spellDamage';
        $result['SPELLDAMAGEBONUSRAW'] = 'rawNeutralSpellDamage';
        $result['HEALTHREGEN'] = 'healthRegen';
        $result['HEALTHREGENRAW'] = 'rawHealthRegen';
        $result['HEALTHBONUS'] = 'rawHealth';
        $result['POISON'] = 'poison';
        $result['LIFESTEAL'] = 'lifeSteal';
        $result['MANAREGEN'] = 'manaRegen';
        $result['MANASTEAL'] = 'manaSteal';
        $result['SPELL_COST_PCT_1'] = '1stSpellCost';
        $result['SPELL_COST_RAW_1'] = 'raw1stSpellCost';
        $result['SPELL_COST_PCT_2'] = '2ndSpellCost';
        $result['SPELL_COST_RAW_2'] = 'raw2ndSpellCost';
        $result['SPELL_COST_PCT_3'] = '3rdSpellCost';
        $result['SPELL_COST_RAW_3'] = 'raw3rdSpellCost';
        $result['SPELL_COST_PCT_4'] = '4thSpellCost';
        $result['SPELL_COST_RAW_4'] = 'raw4thSpellCost';
        $result['THORNS'] = 'thorns';
        $result['REFLECTION'] = 'reflection';
        $result['ATTACKSPEED'] = 'attackSpeed';
        $result['SPEED'] = 'walkSpeed';
        $result['EXPLODING'] = 'exploding';
        $result['SOULPOINTS'] = 'soulPointRegen';
        $result['STAMINA'] = 'sprint';
        $result['STAMINA_REGEN'] = 'sprintRegen';
        $result['JUMP_HEIGHT'] = 'rawJumpHeight';
        $result['XPBONUS'] = 'xpBonus';
        $result['LOOTBONUS'] = 'lootBonus';
        $result['EMERALDSTEALING'] = 'stealing';
        $result['EARTHDAMAGEBONUS'] = 'earthDamage';
        $result['THUNDERDAMAGEBONUS'] = 'thunderDamage';
        $result['WATERDAMAGEBONUS'] = 'waterDamage';
        $result['FIREDAMAGEBONUS'] = 'fireDamage';
        $result['AIRDAMAGEBONUS'] = 'airDamage';
        $result['EARTHDEFENSE'] = 'earthDefence';
        $result['THUNDERDEFENSE'] = 'thunderDefence';
        $result['WATERDEFENSE'] = 'waterDefence';
        $result['FIREDEFENSE'] = 'fireDefence';
        $result['AIRDEFENSE'] = 'airDefence';
        $result['SPELLTHUNDERDAMAGEBONUSRAW'] = 'rawThunderSpellDamage';
        $result['SPELLFIREDAMAGEBONUSRAW'] = 'rawFireSpellDamage';
        $result['SPELLWATERDAMAGEBONUSRAW'] = 'rawWaterSpellDamage';
        $result['SPELLAIRDAMAGEBONUSRAW'] = 'rawAirSpellDamage';
        $result['SPELLEARTHDAMAGEBONUSRAW'] = 'rawEarthSpellDamage';

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
            'spellCostPct1', 'spellCost1Pct' => '1stSpellCost',
            'spellCostPct2', 'spellCost2Pct' => '2ndSpellCost',
            'spellCostPct3', 'spellCost3Pct' => '3rdSpellCost',
            'spellCostPct4', 'spellCost4Pct' => '4thSpellCost',
            'spellCostRaw1' => 'raw1stSpellCost',
            'spellCostRaw2' => 'raw2ndSpellCost',
            'spellCostRaw3' => 'raw3rdSpellCost',
            'spellCostRaw4' => 'raw4thSpellCost',
            'spellDamageBonusRaw' => 'rawNeutralSpellDamage',
            'mainAttackDamageBonusRaw' => 'rawMainAttackNeutralDamage',
            'mainAttackDamageBonus' => 'mainAttackDamage',
            'healthRegenRaw' => 'rawHealthRegen',
            'healthBonus' => 'rawHealth',
            'speed' => 'walkSpeed',
            'soulPoints' => 'soulPointRegen',
            'emeraldStealing' => 'stealing',
            'strengthPoints' => 'rawStrength',
            'dexterityPoints' => 'rawDexterity',
            'intelligencePoints' => 'rawIntelligence',
            'defensePoints' => 'rawDefence',
            'agilityPoints' => 'rawAgility',
            'bonusEarthDamage', 'earthDamageBonus' => 'earthDamage',
            'bonusThunderDamage', 'thunderDamageBonus' => 'thunderDamage',
            'bonusWaterDamage', 'waterDamageBonus' => 'waterDamage',
            'bonusFireDamage', 'fireDamageBonus' => 'fireDamage',
            'bonusAirDamage', 'airDamageBonus' => 'airDamage',
            'bonusEarthDefense', 'earthDefenseBonus' => 'earthDefence',
            'bonusThunderDefense', 'thunderDefenseBonus' => 'thunderDefence',
            'bonusWaterDefense', 'waterDefenseBonus' => 'waterDefence',
            'bonusFireDefense', 'fireDefenseBonus' => 'fireDefence',
            'bonusAirDefense', 'airDefenseBonus' => 'airDefence',
            'jumpHeight' => 'rawJumpHeight',
            'spellElementalDamageBonusRaw' => 'rawSpellDamage',
            'spellThunderDamageBonusRaw' => 'rawThunderSpellDamage',
            'spellFireDamageBonusRaw' => 'rawFireSpellDamage',
            'spellAirDamageBonusRaw' => 'rawAirSpellDamage',
            'spellEarthDamageBonusRaw' => 'rawEarthSpellDamage',
            'spellWaterDamageBonusRaw' => 'rawWaterSpellDamage',
            'attackSpeedBonus' => 'attackSpeed',
            //same ones
            'gatherXpBonus' => 'gatherXpBonus',
            'spellDamageBonus' => 'spellDamage',
            'healthRegen' => 'healthRegen',
            'poison' => 'poison',
            'lifeSteal' => 'lifeSteal',
            'manaRegen' => 'manaRegen',
            'exploding' => 'exploding',
            'sprint' => 'sprint',
            'sprintRegen' => 'sprintRegen',
            'lootBonus' => 'lootBonus',
            'lootQuality' => 'lootQuality',
            'gatherSpeed' => 'gatherSpeed',
            'xpBonus' => 'xpBonus',
            'manaSteal' => 'manaSteal',
            'thorns' => 'thorns',
            'reflection' => 'reflection',
            default => null,
        };
    }
}
