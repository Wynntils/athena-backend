<?php

namespace App\Http\Libraries;

class IngredientManager
{

    public static function convertIngredient(array $items): object
    {
        $input = collect($items);

        $result = $statuses = $itemModifiers = $ingredientModifiers = $itemInfo = [];

        $result['name'] = $input['name'];
        $result['tier'] = $input['tier'];
        $result['level'] = $input['level'];
        $result['untradeable'] = $input->get('untradeable', false);

        $sprite = $input['sprite'];
        $result['material'] = $sprite['id'].':'.$sprite['damage'];
        $result['itemInfo'] = &$itemInfo;
        ItemManager::enhanceWithNewMaterial($result['material'], $itemInfo);

        $result['professions'] = $input['skills'];

        $result['statuses'] = &$statuses;
        foreach ($input['identifications'] as $key => $identification) {
            $translatedName = self::translateStatusName($key);
            $status = &$statuses[$translatedName];
            $status['type'] = getStatusType($translatedName);
            $status['minimum'] = $identification['minimum'];
            $status['maximum'] = $identification['maximum'];
            $status = cleanNull($status);
        }

        $itemIDs = collect($input['itemOnlyIDs']);
        $consumableIDs = collect($input->get('consumableOnlyIDs', []));

        $result['itemModifiers'] = &$itemModifiers;
        $itemModifiers['durability'] = ignoreZero($itemIDs->get('durabilityModifier'));
        $itemModifiers['duration'] = ignoreZero($consumableIDs->get('duration'));
        $itemModifiers['charges'] = ignoreZero($consumableIDs->get('charges'));

        $itemModifiers['strength'] = ignoreZero($itemIDs->get('strengthRequirement'));
        $itemModifiers['dexterity'] = ignoreZero($itemIDs->get('dexterityRequirement'));
        $itemModifiers['intelligence'] = ignoreZero($itemIDs->get('intelligenceRequirement'));
        $itemModifiers['defense'] = ignoreZero($itemIDs->get('defenceRequirement'));
        $itemModifiers['agility'] = ignoreZero($itemIDs->get('agilityRequirement'));

        $modifiers = collect($input['ingredientPositionModifiers']);

        $result['ingredientModifiers'] = &$ingredientModifiers;
        $ingredientModifiers['left'] = ignoreZero($modifiers->get('left'));
        $ingredientModifiers['right'] = ignoreZero($modifiers->get('right'));
        $ingredientModifiers['above'] = ignoreZero($modifiers->get('above'));
        $ingredientModifiers['under'] = ignoreZero($modifiers->get('under'));
        $ingredientModifiers['touching'] = ignoreZero($modifiers->get('touching'));
        $ingredientModifiers['notTouching'] = ignoreZero($modifiers->get('notTouching'));

        $itemModifiers = cleanNull($itemModifiers);
        $ingredientModifiers = cleanNull($ingredientModifiers);
        $statuses = cleanNull($statuses);

        return cleanNull($result);
    }

    private static function translateStatusName(string $raw): ?string
    {
        return match ($raw) {
            'STRENGTHPOINTS' => 'rawStrength',
            'DEXTERITYPOINTS' => 'rawDexterity',
            'INTELLIGENCEPOINTS' => 'rawIntelligence',
            'DEFENSEPOINTS' => 'rawDefence',
            'AGILITYPOINTS' => 'rawAgility',
            // second group {attack stuff}
            'ATTACKSPEED' => 'attackSpeed',
            'DAMAGEBONUSRAW' => 'rawMainAttackDamage',
            'DAMAGEBONUS' => 'mainAttackDamage',
            'MAIN_ATTACK_NEUTRAL_DAMAGE_BONUS_RAW' => 'rawNeutralMainAttackDamage',
            'MAIN_ATTACK_NEUTRAL_DAMAGE_BONUS' => 'neutralMainAttackDamage',
            'MAIN_ATTACK_EARTH_DAMAGE_BONUS_RAW' => 'rawEarthMainAttackDamage',
            'MAIN_ATTACK_EARTH_DAMAGE_BONUS' => 'earthMainAttackDamage',
            'MAIN_ATTACK_THUNDER_DAMAGE_BONUS_RAW' => 'rawThunderMainAttackDamage',
            'MAIN_ATTACK_THUNDER_DAMAGE_BONUS' => 'thunderMainAttackDamage',
            'MAIN_ATTACK_WATER_DAMAGE_BONUS_RAW' => 'rawWaterMainAttackDamage',
            'MAIN_ATTACK_WATER_DAMAGE_BONUS' => 'waterMainAttackDamage',
            'MAIN_ATTACK_FIRE_DAMAGE_BONUS_RAW' => 'rawFireMainAttackDamage',
            'MAIN_ATTACK_FIRE_DAMAGE_BONUS' => 'fireMainAttackDamage',
            'MAIN_ATTACK_AIR_DAMAGE_BONUS_RAW' => 'rawAirMainAttackDamage',
            'MAIN_ATTACK_AIR_DAMAGE_BONUS' => 'airMainAttackDamage',
            'MAIN_ATTACK_ELEMENTAL_DAMAGE_BONUS_RAW' => 'rawElementalMainAttackDamage',
            'MAIN_ATTACK_ELEMENTAL_DAMAGE_BONUS' => 'elementalMainAttackDamage',
            'SPELLDAMAGERAW' => 'rawSpellDamage',
            'SPELLDAMAGE' => 'spellDamage',
            'SPELL_NEUTRAL_DAMAGE_BONUS_RAW' => 'rawNeutralSpellDamage',
            'SPELL_NEUTRAL_DAMAGE_BONUS' => 'neutralSpellDamage',
            'SPELL_EARTH_DAMAGE_BONUS_RAW' => 'rawEarthSpellDamage',
            'SPELL_EARTH_DAMAGE_BONUS' => 'earthSpellDamage',
            'SPELL_THUNDER_DAMAGE_BONUS_RAW' => 'rawThunderSpellDamage',
            'SPELL_THUNDER_DAMAGE_BONUS' => 'thunderSpellDamage',
            'SPELL_WATER_DAMAGE_BONUS_RAW' => 'rawWaterSpellDamage',
            'SPELL_WATER_DAMAGE_BONUS' => 'waterSpellDamage',
            'SPELL_FIRE_DAMAGE_BONUS_RAW' => 'rawFireSpellDamage',
            'SPELL_FIRE_DAMAGE_BONUS' => 'fireSpellDamage',
            'SPELL_AIR_DAMAGE_BONUS_RAW' => 'rawAirSpellDamage',
            'SPELL_AIR_DAMAGE_BONUS' => 'airSpellDamage',
            'RAINBOWSPELLDAMAGERAW' => 'rawElementalSpellDamage',
            'SPELL_ELEMENTAL_DAMAGE_BONUS' => 'elementalSpellDamage',
            // third group {health/mana stuff}
            'HEALTHBONUS' => 'rawHealth',
            'HEALTHREGENRAW' => 'rawHealthRegen',
            'HEALTHREGEN' => 'healthRegen',
            'LIFESTEAL' => 'lifeSteal',
            'MANAREGEN' => 'manaRegen',
            'MANASTEAL' => 'manaSteal',
            // fourth group {damage stuff}
            'DAMAGE_BONUS_RAW' => 'rawDamage',
            'DAMAGE_BONUS' => 'damage',
            'NEUTRAL_DAMAGE_BONUS_RAW' => 'rawNeutralDamage',
            'NEUTRAL_DAMAGE_BONUS' => 'neutralDamage',
            'EARTH_DAMAGE_BONUS_RAW' => 'rawEarthDamage',
            'EARTHDAMAGEBONUS' => 'earthDamage',
            'THUNDER_DAMAGE_BONUS_RAW' => 'rawThunderDamage',
            'THUNDERDAMAGEBONUS' => 'thunderDamage',
            'WATER_DAMAGE_BONUS_RAW' => 'rawWaterDamage',
            'WATERDAMAGEBONUS' => 'waterDamage',
            'FIRE_DAMAGE_BONUS_RAW' => 'rawFireDamage',
            'FIREDAMAGEBONUS' => 'fireDamage',
            'AIR_DAMAGE_BONUS_RAW' => 'rawAirDamage',
            'AIRDAMAGEBONUS' => 'airDamage',
            'ELEMENTAL_DAMAGE_BONUS_RAW' => 'rawElementalDamage',
            'ELEMENTAL_DAMAGE_BONUS' => 'elementalDamage',
            // fifth group {defence stuff}
            'EARTHDEFENSE' => 'earthDefence',
            'THUNDERDEFENSE' => 'thunderDefence',
            'WATERDEFENSE' => 'waterDefence',
            'FIREDEFENSE' => 'fireDefence',
            'AIRDEFENSE' => 'airDefence',
            // sixth group {passive damage}
            'EXPLODING' => 'exploding',
            'POISON' => 'poison',
            'THORNS' => 'thorns',
            'REFLECTION' => 'reflection',
            // seventh group {movement stuff}
            'SPEED' => 'walkSpeed',
            'STAMINA' => 'sprint',
            'STAMINA_REGEN' => 'sprintRegen',
            'JUMP_HEIGHT' => 'rawJumpHeight',
            // eighth group {xp/gathering stuff}
            'SOULPOINTS' => 'soulPointRegen',
            'LOOTBONUS' => 'lootBonus',
            'LOOT_QUALITY' => 'lootQuality',
            'EMERALDSTEALING' => 'stealing',
            'XPBONUS' => 'xpBonus',
            'GATHER_XP_BONUS' => 'gatherXPBonus',
            'GATHER_SPEED' => 'gatherSpeed',
            // ninth group {spell stuff}
            'SPELL_COST_RAW_1' => 'raw1stSpellCost',
            'SPELL_COST_PCT_1' => '1stSpellCost',
            'SPELL_COST_RAW_2' => 'raw2ndSpellCost',
            'SPELL_COST_PCT_2' => '2ndSpellCost',
            'SPELL_COST_RAW_3' => 'raw3rdSpellCost',
            'SPELL_COST_PCT_3' => '3rdSpellCost',
            'SPELL_COST_RAW_4' => 'raw4thSpellCost',
            'SPELL_COST_PCT_4' => '4thSpellCost',

            default => null,
        };
    }

    public static function getHeadTextures(): array
    {
        $result = [];

        $result["Royal Cake Slice"] = "eyJ0ZXh0dXJlcyI6eyJTS0lOIjp7InVybCI6Imh0dHA6Ly90ZXh0dXJlcy5taW5lY3JhZnQubmV0L3RleHR1cmUvZjkxMzY1MTRmMzQyZTdjNTIwOGExNDIyNTA2YTg2NjE1OGVmODRkMmIyNDkyMjAxMzllOGJmNjAzMmUxOTMifX19";
        $result["Dead Bee"] = "eyJ0ZXh0dXJlcyI6eyJTS0lOIjp7InVybCI6Imh0dHA6Ly90ZXh0dXJlcy5taW5lY3JhZnQubmV0L3RleHR1cmUvOTQ3MzIyZjgzMWUzYzE2OGNmYmQzZTI4ZmU5MjUxNDRiMjYxZTc5ZWIzOWM3NzEzNDlmYWM1NWE4MTI2NDczIn19fQ==";
        $result["Nivlan Honeycomb"] = "eyJ0ZXh0dXJlcyI6eyJTS0lOIjp7InVybCI6Imh0dHA6Ly90ZXh0dXJlcy5taW5lY3JhZnQubmV0L3RleHR1cmUvNjkzYzM1MTJmYzU4ODVmY2NiYjI1ZDJkYWY3ZmRjZmFlODI2NDFlZDdlNWUzNTk3Y2RkZjczZTQxMTU5ZjI0In19fQ==";
        $result["Vibrant Augment"] = "eyJ0ZXh0dXJlcyI6eyJTS0lOIjp7InVybCI6Imh0dHA6Ly90ZXh0dXJlcy5taW5lY3JhZnQubmV0L3RleHR1cmUvZTM5OWU0N2U1NWMxNWQzZTI2NTI1YzVhNDcwZjI0OGUzMzVkMmZhYTNhNjM0MTBjNGRkZjQyZGEzNmFkMmYifX19";
        $result["Nivlan Honey"] = "eyJ0ZXh0dXJlcyI6eyJTS0lOIjp7InVybCI6Imh0dHA6Ly90ZXh0dXJlcy5taW5lY3JhZnQubmV0L3RleHR1cmUvZTZkY2M4ZjM3YWM5OWQ5NTFlY2JjNWRmNWU4NTgyMTMzZjVmMjMwN2U3NjlhZjZiNmNmZmY0MjgyMTgwNjcifX19";
        $result["Burnt Skull"] = "eyJ0ZXh0dXJlcyI6eyJTS0lOIjp7InVybCI6Imh0dHA6Ly90ZXh0dXJlcy5taW5lY3JhZnQubmV0L3RleHR1cmUvNzk1M2I2YzY4NDQ4ZTdlNmI2YmY4ZmIyNzNkNzIwM2FjZDhlMWJlMTllODE0ODFlYWQ1MWY0NWRlNTlhOCJ9fX0=";
        $result["Crumbling Skull"] = "eyJ0ZXh0dXJlcyI6eyJTS0lOIjp7InVybCI6Imh0dHA6Ly90ZXh0dXJlcy5taW5lY3JhZnQubmV0L3RleHR1cmUvNzk1M2I2YzY4NDQ4ZTdlNmI2YmY4ZmIyNzNkNzIwM2FjZDhlMWJlMTllODE0ODFlYWQ1MWY0NWRlNTlhOCJ9fX0=";
        $result["Coagulated Soulmass"] = "eyJ0ZXh0dXJlcyI6eyJTS0lOIjp7InVybCI6Imh0dHA6Ly90ZXh0dXJlcy5taW5lY3JhZnQubmV0L3RleHR1cmUvMWVhNmY5MzJiNDVmZGYzYjY5M2Q5ZTQ0YmQwNWJjYTM2NGViNWI5YWZmNDk3MjI2ZmRiNTJhYmIyNDM2NDIyIn19fQ==";
        $result["Rotten Log"] = "eyJ0ZXh0dXJlcyI6eyJTS0lOIjp7InVybCI6Imh0dHA6Ly90ZXh0dXJlcy5taW5lY3JhZnQubmV0L3RleHR1cmUvMmE5NWRkMmVkYTAzY2E0OTE3OTZmNTM4ZDYyNTU3OWE4MjhiNzE4NDNmYjYwODY5YjEyYzkxZWNlMTUxNiJ9fX0=";
        $result["Victim's Skull"] = "eyJ0ZXh0dXJlcyI6eyJTS0lOIjp7InVybCI6Imh0dHA6Ly90ZXh0dXJlcy5taW5lY3JhZnQubmV0L3RleHR1cmUvMWFlMzg1NWY5NTJjZDRhMDNjMTQ4YTk0NmUzZjgxMmE1OTU1YWQzNWNiY2I1MjYyN2VhNGFjZDQ3ZDMwODEifX19";
        $result["Native Jadeite"] = "eyJ0ZXh0dXJlcyI6eyJTS0lOIjp7InVybCI6Imh0dHA6Ly90ZXh0dXJlcy5taW5lY3JhZnQubmV0L3RleHR1cmUvY2U2NThjZTdjNDI5MWVmZTUwYjIxZDEzZjk3MmU5M2MzNzJkMjcyMWVkN2Q2NTI2NjA2MGE0OTE2YTM1YiJ9fX0=";
        $result["Decaying Heart"] = "eyJ0ZXh0dXJlcyI6eyJTS0lOIjp7InVybCI6Imh0dHA6Ly90ZXh0dXJlcy5taW5lY3JhZnQubmV0L3RleHR1cmUvOTZjODQ0N2E4YjZiMGUwYzdlNzYyOWM2ODk4ZWM5Yzc0OWE3YTBhMmI0NTJiOWMzODUyYzc4NDdiYjRkYzUifX19";
        $result["Squid"] = "eyJ0ZXh0dXJlcyI6eyJTS0lOIjp7InVybCI6Imh0dHA6Ly90ZXh0dXJlcy5taW5lY3JhZnQubmV0L3RleHR1cmUvZGYzM2UzODlkZGJlM2M4ZGQzMzJmMWI2ZmQ2MGM0MmIyMjRlOGQyOGI1YzZkZDllNTBmYWMyNGM1OTE2Zjk1MSJ9fX0=";
        $result["More-Pearlescent Jewel"] = "eyJ0ZXh0dXJlcyI6eyJTS0lOIjp7InVybCI6Imh0dHA6Ly90ZXh0dXJlcy5taW5lY3JhZnQubmV0L3RleHR1cmUvYmE0NGUyYjgzODhkZjU5M2E4NWY3ZDU0NDQ1Y2U3ODc5NmNlZTZiMGJjYjdhNTVhY2JkZDFjYmQ4ZjM1YWVmMiJ9fX0=";
        $result["Elemental Crystal"] = "eyJ0ZXh0dXJlcyI6eyJTS0lOIjp7InVybCI6Imh0dHA6Ly90ZXh0dXJlcy5taW5lY3JhZnQubmV0L3RleHR1cmUvZTY3OTliZmFhM2EyYzYzYWQ4NWRkMzc4ZTY2ZDU3ZDlhOTdhM2Y4NmQwZDlmNjgzYzQ5ODYzMmY0ZjVjIn19fQ==";
        $result["Obelisk Core"] = "eyJ0ZXh0dXJlcyI6eyJTS0lOIjp7InVybCI6Imh0dHA6Ly90ZXh0dXJlcy5taW5lY3JhZnQubmV0L3RleHR1cmUvOWYwZDczMTUwZGY0ZjVkMDc3NWI1NTQ4OWEyOGE5Nzc2NGFmNWE3NzUxYTAwZTQwNDA0OTVlOTdmZmU5NzcyZiJ9fX0=";
        $result["Pig's Skull"] = "eyJ0ZXh0dXJlcyI6eyJTS0lOIjp7InVybCI6Imh0dHA6Ly90ZXh0dXJlcy5taW5lY3JhZnQubmV0L3RleHR1cmUvNTUyN2M0ZGE4OTIyZDM5NDVjNzFkMzFkMGFhYTY3NjE0ODlkMmU3YzVlZTJhNzFjYmJjYmMwODA2ODczZjAifX19";
        $result["Cyclops Eye"] = "eyJ0ZXh0dXJlcyI6eyJTS0lOIjp7InVybCI6Imh0dHA6Ly90ZXh0dXJlcy5taW5lY3JhZnQubmV0L3RleHR1cmUvMTdkYjE5MjNkMDNjNGVmNGU5ZjZlODcyYzVhNmFkMjU3OGIxYWZmMmIyODFmYmMzZmZhNzQ2NmM4MjVmYjkifX19";
        $result["Tempered Core"] = "eyJ0ZXh0dXJlcyI6eyJTS0lOIjp7InVybCI6Imh0dHA6Ly90ZXh0dXJlcy5taW5lY3JhZnQubmV0L3RleHR1cmUvYzM2ODdlMjVjNjMyYmNlOGFhNjFlMGQ2NGMyNGU2OTRjM2VlYTYyOWVhOTQ0ZjRjZjMwZGNmYjRmYmNlMDcxIn19fQ==";
        $result["Pride of the Heights"] = "eyJ0ZXh0dXJlcyI6eyJTS0lOIjp7InVybCI6Imh0dHA6Ly90ZXh0dXJlcy5taW5lY3JhZnQubmV0L3RleHR1cmUvOWMyZTlkODM5NWNhY2Q5OTIyODY5YzE1MzczY2Y3Y2IxNmRhMGE1Y2U1ZjNjNjMyYjE5Y2ViMzkyOWM5YTExIn19fQ==";
        $result["Blighted Skull"] = "eyJ0ZXh0dXJlcyI6eyJTS0lOIjp7InVybCI6Imh0dHA6Ly90ZXh0dXJlcy5taW5lY3JhZnQubmV0L3RleHR1cmUvNzk1M2I2YzY4NDQ4ZTdlNmI2YmY4ZmIyNzNkNzIwM2FjZDhlMWJlMTllODE0ODFlYWQ1MWY0NWRlNTlhOCJ9fX0=";
        $result["Contorted Stone"] = "eyJ0ZXh0dXJlcyI6eyJTS0lOIjp7InVybCI6Imh0dHA6Ly90ZXh0dXJlcy5taW5lY3JhZnQubmV0L3RleHR1cmUvNzg0MGI4N2Q1MjI3MWQyYTc1NWRlZGM4Mjg3N2UwZWQzZGY2N2RjYzQyZWE0NzllYzE0NjE3NmIwMjc3OWE1In19fQ==";
        $result["Urdar's Stone"] = "eyJ0ZXh0dXJlcyI6eyJTS0lOIjp7InVybCI6Imh0dHA6Ly90ZXh0dXJlcy5taW5lY3JhZnQubmV0L3RleHR1cmUvOTZjMzNiMmIzYmY1ZDNmMDU4MmMyYmUzYmNkYmYzMjQ1YmRjYzQyNjNiNjY1MjM4ZmJkMWJmNDg2YmNjIn19fQ==";
        $result["Engored Oculus"] = "eyJ0ZXh0dXJlcyI6eyJTS0lOIjp7InVybCI6Imh0dHA6Ly90ZXh0dXJlcy5taW5lY3JhZnQubmV0L3RleHR1cmUvNzQzZWNkOGUxN2EzZGFhNjY1ZGIzZGIwODIyNzJjNjI5Nzc5OGViYjUwOTY0MDI2Y2U3Zjg1NDU4YmY1NTY2YyJ9fX0=";
        $result["Borange Fluff"] = "eyJ0ZXh0dXJlcyI6eyJTS0lOIjp7InVybCI6Imh0dHA6Ly90ZXh0dXJlcy5taW5lY3JhZnQubmV0L3RleHR1cmUvYTBmYjRlNmE2MTMyMmE0MTg1ZTdiNDFhNjQzZjZhNGNiNjlhMzE3ZWJlMzkzOTZmYjI3NjI3ZTM2MTRjNjkifX19";
        $result["Vortexian Event Horizon"] = "eyJ0ZXh0dXJlcyI6eyJTS0lOIjp7InVybCI6Imh0dHA6Ly90ZXh0dXJlcy5taW5lY3JhZnQubmV0L3RleHR1cmUvOWRkZWJiYjA2MmY2YTM4NWE5MWNhMDVmMThmNWMwYWNiZTMzZTJkMDZlZTllNzQxNmNlZjZlZTQzZGZlMmZiIn19fQ==";
        $result["Shrieker's Head"] = "eyJ0ZXh0dXJlcyI6eyJTS0lOIjp7InVybCI6Imh0dHA6Ly90ZXh0dXJlcy5taW5lY3JhZnQubmV0L3RleHR1cmUvYWJiN2M2OTYxZDdiOTRkODRkMDRmNjQxYzRjMDgzMDIwOTZiYzFmMmQwY2RiODdiMTA3MDBhOTEwYzA0N2IyOSJ9fX0=";
        // 1.20
        $result["Ice Cream Sandwich"] = "ewogICJ0aW1lc3RhbXAiIDogMTYwMjk5NTU5ODI2OCwKICAicHJvZmlsZUlkIiA6ICJhYzE2NGNkYzNkYjc0ZWQxYThiYzU1MWIxZTVlMzgwYiIsCiAgInByb2ZpbGVOYW1lIiA6ICJuaWNrdHJlZSIsCiAgInNpZ25hdHVyZVJlcXVpcmVkIiA6IHRydWUsCiAgInRleHR1cmVzIiA6IHsKICAgICJTS0lOIiA6IHsKICAgICAgInVybCIgOiAiaHR0cDovL3RleHR1cmVzLm1pbmVjcmFmdC5uZXQvdGV4dHVyZS9jYWM2ZmQyNWE0N2MwOGMxMWVkNDg5YjFjYzMyZmZkM2ZiM2UxNTAzZDNkNDc2ZmMyMTMxZjdlMGE2ZmI0ODAxIgogICAgfQogIH0KfQ==";
        $result["Letvus Delight"] = "eyJ0ZXh0dXJlcyI6eyJTS0lOIjp7InVybCI6Imh0dHA6Ly90ZXh0dXJlcy5taW5lY3JhZnQubmV0L3RleHR1cmUvZWJmNDgxYmZlMDQ1NWM3NjdkZmQ3MGYxYzlmNjQ3ZGE0ZjhhZTQ1OWU0ZmU2NGVmOTQ3OTBiZDM3NGQ0NjE0In19fQ==";
        $result["Mellow Mango"] = "eyJ0ZXh0dXJlcyI6eyJTS0lOIjp7InVybCI6Imh0dHA6Ly90ZXh0dXJlcy5taW5lY3JhZnQubmV0L3RleHR1cmUvOWZkMGUzMzBhNjg4ZDhiYjk1MTliZWZlMWJmYzM0MzM3YjM3MWFjNzUxNTAyMTZmZGQwMzk1OWViN2I0NCJ9fX0=";
        $result["Festering Face"] = "eyJ0aW1lc3RhbXAiOjE1NzIwMDcwNzUwMTUsInByb2ZpbGVJZCI6IjE5MmE5MzE3MDNiODQzYzZiN2ZjNDI1NjIxNzE3MDViIiwicHJvZmlsZU5hbWUiOiI4NHYiLCJzaWduYXR1cmVSZXF1aXJlZCI6dHJ1ZSwic2lnbmF0dXJlUmVxdWlyZWQiOnRydWUsInRleHR1cmVzIjp7IlNLSU4iOnsidXJsIjoiaHR0cDovL3RleHR1cmVzLm1pbmVjcmFmdC5uZXQvdGV4dHVyZS9jZTFlYTExYjczOGNmNDQ0Zjc2OTQ1ZjU4NmEwODQ1MTY0MTIxNjZiYTllMDI1MGRmMjhjZTNmNjE0MzU5ZTI0In19fQ==";
        $result["Autonomous Core"] = "eyJ0ZXh0dXJlcyI6eyJTS0lOIjp7InVybCI6Imh0dHA6Ly90ZXh0dXJlcy5taW5lY3JhZnQubmV0L3RleHR1cmUvOGRhMzMyYWJkZTMzM2ExNWE2YzZmY2ZlY2E4M2YwMTU5ZWE5NGI2OGU4ZjI3NGJhZmMwNDg5MmI2ZGJmYyJ9fX0=";
        $result["Coalescence"] = "eyJ0aW1lc3RhbXAiOjE1NDQzOTU4MzA5NTUsInByb2ZpbGVJZCI6IjQ5ODU2NTcwYWUwODRlYWY5OTg4MDQwY2VhZGI5ZGQxIiwicHJvZmlsZU5hbWUiOiJTbmVycCIsInNpZ25hdHVyZVJlcXVpcmVkIjp0cnVlLCJ0ZXh0dXJlcyI6eyJTS0lOIjp7InVybCI6Imh0dHA6Ly90ZXh0dXJlcy5taW5lY3JhZnQubmV0L3RleHR1cmUvOWM3N2EyOTZmNjc4NzMzMjE4YzFiOWVkOWJjNmFhY2Y4NDE1MGI4MTM2OWQ5ZmUxNWUzN2IzMjk0NmY4NTEyYSJ9fX0=";
        $result["Gaze of Darkness"] = "eyJ0aW1lc3RhbXAiOjE0NDk1MDYwNTE1NTksInByb2ZpbGVJZCI6IjY2ODYxMDY1YzMzYjQ4MGNhOWQ0MWJiODlkYjcxMDhjIiwicHJvZmlsZU5hbWUiOiJEYXJrbmVzc2ZhbGwiLCJ0ZXh0dXJlcyI6eyJTS0lOIjp7InVybCI6Imh0dHA6Ly90ZXh0dXJlcy5taW5lY3JhZnQubmV0L3RleHR1cmUvMzg3ZTQ3OTJmZjY1NDJkY2FiMThmOWY0OWY0OTMzM2YxM2ViMTAzOTRjNDQ4YWMxNDUzOTY3ODYyYzhmMzgifX19";

        // 1.20.?
        $result["Shattered Aspect"] = "eyJ0aW1lc3RhbXAiOjE0NjE1NDgzMDAyMjgsInByb2ZpbGVJZCI6IjUwNzk4YjJhN2QxYjQxYTRiNmFlYzRiNWUwOWJjMTcxIiwicHJvZmlsZU5hbWUiOiJFbHN3ZXlyIiwidGV4dHVyZXMiOnsiU0tJTiI6eyJ1cmwiOiJodHRwOi8vdGV4dHVyZXMubWluZWNyYWZ0Lm5ldC90ZXh0dXJlLzQzNWY3MGZjMmMzMTNmNjhlN2NkNDBkMmQ1MjQzZmFjNDhhMjMwNzg3YmY5ZTBlMTU1ODZjZGU3NmIxM2FiNCJ9fX0=";
        $result["Disturbed Aspect"] = "eyJ0aW1lc3RhbXAiOjE0Mzc2OTk0NjIwNTMsInByb2ZpbGVJZCI6IjUwNzk4YjJhN2QxYjQxYTRiNmFlYzRiNWUwOWJjMTcxIiwicHJvZmlsZU5hbWUiOiJFbHN3ZXlyIiwidGV4dHVyZXMiOnsiU0tJTiI6eyJ1cmwiOiJodHRwOi8vdGV4dHVyZXMubWluZWNyYWZ0Lm5ldC90ZXh0dXJlL2Y3MThiZjc5M2U3NjFiODIyNWQwZGQwNGEzMDk0MzRhZGY3ZTM0Y2I5Y2E4OTVlZjNiMzE2M2U1NjI4MjUifX19=";
        $result["Atmospheric Aspect"] = "eyJ0ZXh0dXJlcyI6eyJTS0lOIjp7InVybCI6Imh0dHA6Ly90ZXh0dXJlcy5taW5lY3JhZnQubmV0L3RleHR1cmUvYWE2ZGQ3NWY0MWU0MjY4ZTBhMTI2OTA1MDkwN2FhNjc0NmZmZDM3YTRhOTI5ZTczMjUyNDY0MmMzMzZiYyJ9fX0=";
        $result["Wintery Aspect"] = "eyJ0ZXh0dXJlcyI6eyJTS0lOIjp7InVybCI6Imh0dHA6Ly90ZXh0dXJlcy5taW5lY3JhZnQubmV0L3RleHR1cmUvODdkNjZmOTM5MDlhNmQ0NjQxYzY1MzA4MmUwNDc0OTY5MWRlODJjZjc3MjMyYmQyMGFiMzJhZGY0ZiJ9fX0=";
        $result["Evaporated Aspect"] = "eyJ0ZXh0dXJlcyI6eyJTS0lOIjp7InVybCI6Imh0dHA6Ly90ZXh0dXJlcy5taW5lY3JhZnQubmV0L3RleHR1cmUvMmIzNTFjOWRhZDJjYWU2MzNlODI4YzU0N2M0ODJmZTY3YTg1NzlmYzRhMzhlMWQ2Yzg2YzRjMGVhZjAxZiJ9fX0=";
        $result["Overload Aspect"] = "eyJ0ZXh0dXJlcyI6eyJTS0lOIjp7InVybCI6Imh0dHA6Ly90ZXh0dXJlcy5taW5lY3JhZnQubmV0L3RleHR1cmUvYzY3OWVmYzU5ZTIyZmNmMzRmNzQ0OGJmN2FiNjY2NGY3OTljM2RmZjY1NmNmNDgzMDk4YmUzNmM5YWUxIn19fQ==";
        $result["Repulsive Aspect"] = "eyJ0ZXh0dXJlcyI6eyJTS0lOIjp7InVybCI6Imh0dHA6Ly90ZXh0dXJlcy5taW5lY3JhZnQubmV0L3RleHR1cmUvYTU1YjhhMzdhMjc1NzE3NWY3M2RjZjliZjI1ZWI5ZDI0NDFjODU4OWVhMTEzMWI2NDE3YWEzNDVjMGUzZmM0NyJ9fX0=";
        $result["Compressed Aspect"] = "eyJ0ZXh0dXJlcyI6eyJTS0lOIjp7InVybCI6Imh0dHA6Ly90ZXh0dXJlcy5taW5lY3JhZnQubmV0L3RleHR1cmUvNGVjM2JkMWJlMWI1YzljNWRlYTM1MWY4YzM0ZjlhMDdiZjBlZTc2NDM2NDVhNDQzNWU1MmQ3YTc4NzIwZmIifX19";
        $result["Erratic Aspect"] = "eyJ0ZXh0dXJlcyI6eyJTS0lOIjp7InVybCI6Imh0dHA6Ly90ZXh0dXJlcy5taW5lY3JhZnQubmV0L3RleHR1cmUvMjYyODNlN2E4OGQzMjcxOTMwNGEzN2VkZTBjNmE4YzVkYzlkOWNmOWIwMGExNzljZjkwNGU4Y2U4MjEzMTIifX19";
        $result["Igneous Aspect"] = "eyJ0ZXh0dXJlcyI6eyJTS0lOIjp7InVybCI6Imh0dHA6Ly90ZXh0dXJlcy5taW5lY3JhZnQubmV0L3RleHR1cmUvYzJiMGEyNzA5YWQyN2M1NzgzYmE3YWNiZGFlODc4N2QxNzY3M2YwODg4ZjFiNmQ0ZTI0ZWUxMzI5OGQ0In19fQ==";


        return $result;
    }
}
