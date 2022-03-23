<?php

namespace App\Http\Libraries;

use App\Http\Enums\MajorIdentifications;

class ItemManager
{

    public static function convertItem(array $item): array
    {
        $result = [];
        $input = collect($item);

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

        $itemInfo = &$result['itemInfo'];
        $itemInfo['type'] = strtoupper($input->get('type', $input->get('accessoryType')));
        $itemInfo['set'] = $input['set'];
        $itemInfo['dropType'] = strtoupper($input['dropType']);
        $itemInfo['armorColor'] = $input->get('armorColor') === '160,101,64' ? null : $input->get('armorColor');

        $requirements = &$result['requirements'];
        $requirements['quest'] = $input->get('quest');
        $requirements['classType'] = strtoupper($input->get('classRequirement'));
        $requirements['level'] = $input->get('level');
        $requirements['strength'] = $input->get('strength');
        $requirements['dexterity'] = $input->get('dexterity');
        $requirements['intelligence'] = $input->get('intelligence');
        $requirements['defense'] = $input->get('defense');
        $requirements['agility'] = $input->get('agility');

        $damageTypes = &$result['damageTypes'];
        $damageTypes['neutral'] = ignoreZero($input->get('damgae'));
        $damageTypes['earth'] = ignoreZero($input->get('earthDamage'));
        $damageTypes['thunder'] = ignoreZero($input->get('thunderDamage'));
        $damageTypes['water'] = ignoreZero($input->get('waterDamage'));
        $damageTypes['fire'] = ignoreZero($input->get('fireDamage'));
        $damageTypes['air'] = ignoreZero($input->get('airDamage'));

        $defenseTypes = &$result['defenseTypes'];
        $defenseTypes['health'] = ignoreZero($input->get('health'));
        $defenseTypes['earth'] = ignoreZero($input->get('earthDefense'));
        $defenseTypes['thunder'] = ignoreZero($input->get('thunderDefense'));
        $defenseTypes['water'] = ignoreZero($input->get('waterDefense'));
        $defenseTypes['fire'] = ignoreZero($input->get('fireDefense'));
        $defenseTypes['air'] = ignoreZero($input->get('airDefense'));

        $statuses = &$result['statuses'];

        $result['majorIds'] = $input->get('majorIds');
        $result['restriction'] = $input->get('restriction');
        $result['lore'] = $input->get('addedLore');

        foreach ($item as $key => $value) {

            if ($key === 'armorType') {
                $itemInfo['material'] = strtolower(str_replace('chain', 'chainmail', 'minecraft:'.$value.'_'.$itemInfo["type"]));
                continue;
            }

            if ($key === 'material' && $value !== null) {
                $itemInfo['material'] = $value;
                continue;
            }

            if (!is_numeric($value) || $value === 0.0) {
                continue;
            }

            $translatedName = self::translateStatusName($key);
            $status = &$statuses[$translatedName];
            $status['type'] = getStatusType($translatedName);
            $status['isFixed'] = $isFixed($translatedName);
            $status['baseValue'] = $value;
        }

        cleanNull($itemInfo);
        cleanNull($requirements);
        cleanNull($damageTypes);
        cleanNull($defenseTypes);
        cleanNull($statuses);
        cleanNull($result);
        return $result;
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
        //third group {health/mana stuff}
        $order['rawHealth'] = 12;
        $order['rawHealthRegen'] = 13;
        $order['healthRegen'] = 14;
        $order['lifeSteal'] = 15;
        $order['manaRegen'] = 16;
        $order['manaSteal'] = 17;
        //fourth group {damage stuff}
        $order['earthDamage'] = 18;
        $order['thunderDamage'] = 19;
        $order['waterDamage'] = 20;
        $order['fireDamage'] = 21;
        $order['airDamage'] = 22;
        //fifth group {defence stuff}
        $order['earthDefence'] = 23;
        $order['thunderDefence'] = 24;
        $order['waterDefence'] = 25;
        $order['fireDefence'] = 26;
        $order['airDefence'] = 27;
        //sixth group {passive damage}
        $order['exploding'] = 28;
        $order['poison'] = 29;
        $order['thorns'] = 30;
        $order['reflection'] = 31;
        //seventh group {movement stuff}
        $order['walkSpeed'] = 32;
        $order['sprint'] = 33;
        $order['sprintRegen'] = 34;
        $order['rawJumpHeight'] = 35;
        //eigth group {XP/Gathering stuff}
        $order['soulPointRegen'] = 36;
        $order['lootBonus'] = 37;
        $order['lootQuality'] = 38;
        $order['emeraldStealing'] = 39;
        $order['xpBonus'] = 40;
        $order['gatherXPBonus'] = 41;
        $order['gatherSpeed'] = 42;
        //ninth group {spell stuff}
        $order['raw1stSpellCost'] = 43;
        $order['1stSpellCost'] = 44;
        $order['raw2ndSpellCost'] = 45;
        $order['2ndSpellCost'] = 46;
        $order['raw3rdSpellCost'] = 47;
        $order['3rdSpellCost'] = 48;
        $order['raw4thSpellCost'] = 49;
        $order['4thSpellCost'] = 50;

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
        $result['DAMAGEBONUS'] = 'mainAttackDamage';
        $result['DAMAGEBONUSRAW'] = 'rawMainAttackNeutralDamage';
        $result['SPELLDAMAGE'] = 'spellDamage';
        $result['SPELLDAMAGERAW'] = 'rawNeutralSpellDamage';
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
            'spellDamageRaw' => 'rawNeutralSpellDamage',
            'damageBonusRaw' => 'rawMainAttackNeutralDamage',
            'damageBonus' => 'mainAttackDamage',
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
            'bonusEarthDamage' => 'earthDamage',
            'bonusThunderDamage' => 'thunderDamage',
            'bonusWaterDamage' => 'waterDamage',
            'bonusFireDamage' => 'fireDamage',
            'bonusAirDamage' => 'airDamage',
            'bonusEarthDefense' => 'earthDefence',
            'bonusThunderDefense' => 'thunderDefence',
            'bonusWaterDefense' => 'waterDefence',
            'bonusFireDefense' => 'fireDefence',
            'bonusAirDefense' => 'airDefence',
            'jumpHeight' => 'rawJumpHeight',
            'rainbowSpellDamageRaw' => 'rawSpellDamage',
            'gatherXpBonus' => 'gatherXPBonus',
            'attackSpeedBonus' => 'attackSpeed',
            //same ones
            'spellDamage' => 'spellDamage',
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
