<?php

namespace App\Http\Resources\Cache;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Top-10 leaderboards keyed by category.
 *
 * Each category maps a rank ("1" through "10") to either a guild name
 * (guild categories) or a player UUID (player categories). Every category
 * always contains exactly 10 entries. A value of `"redacted"` is returned
 * for player ranks where the player has opted out of public leaderboards.
 *
 * The cache also passes through any additional categories returned by
 * upstream (for example future `guildSeasonN` keys not yet enumerated here),
 * each following the same `array<string, string>` shape.
 */
class LeaderboardCacheResource extends JsonResource
{
    public bool $preserveKeys = true;

    public function toArray(Request $request): array
    {
        // Spread first so dynamic categories that aren't enumerated below
        // (e.g. future seasons) still pass through. The enumerated literals
        // override and give Scramble per-property schemas.
        return [
            ...$this->resource,
            /**
             * Top 10 guilds ranked by guild level.
             *
             * @var array<string, string>
             *
             * @example {"1":"Sequoia","2":"Aequitas","3":"The Aquarium","4":"Avicia","5":"Paladins United","6":"Titans Valor","7":"The Broken Gasmask","8":"Anime Lovers","9":"Nerfuria","10":"Empire of Sindria"}
             */
            'guildLevel' => $this->resource['guildLevel'] ?? [],
            /**
             * Top 10 guilds ranked by total territories currently owned.
             *
             * @var array<string, string>
             *
             * @example {"1":"Aequitas","2":"Avicia","3":"Sequoia","4":"Jasmine Dragon","5":"Empire of Sindria","6":"Infurnace","7":"KongoBoys","8":"Titans Valor","9":"Paladins United","10":"Black Fangs"}
             */
            'guildTerritories' => $this->resource['guildTerritories'] ?? [],
            /**
             * Top 10 guilds ranked by total wars won.
             *
             * @var array<string, string>
             *
             * @example {"1":"Avicia","2":"The Aquarium","3":"Aequitas","4":"Sequoia","5":"Idiot Co","6":"Titans Valor","7":"KongoBoys","8":"Empire of TKW","9":"Empire of Sindria","10":"Nerfuria"}
             */
            'guildWars' => $this->resource['guildWars'] ?? [],
            /**
             * Top 10 guilds ranked by total raids cleared (any raid).
             *
             * @var array<string, string>
             *
             * @example {"1":"Sequoia","2":"Aequitas","3":"Hesperides","4":"The Broken Gasmask","5":"Paladins United","6":"Anime Lovers","7":"The Aquarium","8":"Avicia","9":"Titans Valor","10":"Eden"}
             */
            'guildTotalRaids' => $this->resource['guildTotalRaids'] ?? [],
            /**
             * Top 10 players (by UUID) ranked by combined combat level across all of their characters.
             *
             * @var array<string, string>
             *
             * @example {"1":"1c4246b0-2734-48d3-a9b9-7ca38e31e2a0","2":"72250a1d-144c-48d8-8223-1209ffcaf82d","3":"b7454a9d-ea64-4dea-a4ef-c544d6861b7c"}
             */
            'combatGlobalLevel' => $this->resource['combatGlobalLevel'] ?? [],
            /**
             * Top 10 players (by UUID) ranked by highest single-character combat level.
             *
             * @var array<string, string>
             *
             * @example {"1":"1c4246b0-2734-48d3-a9b9-7ca38e31e2a0","2":"72250a1d-144c-48d8-8223-1209ffcaf82d","3":"b7454a9d-ea64-4dea-a4ef-c544d6861b7c"}
             */
            'combatSoloLevel' => $this->resource['combatSoloLevel'] ?? [],
            /**
             * Top 10 players (by UUID) ranked by combined profession level across all of their characters.
             *
             * @var array<string, string>
             *
             * @example {"1":"redacted","2":"7181f903-7849-49b4-9d5c-2f94ecb67313","3":"63fac5fc-806d-419a-b715-c1e7c60f3ebb"}
             */
            'professionsGlobalLevel' => $this->resource['professionsGlobalLevel'] ?? [],
            /**
             * Top 10 players (by UUID) ranked by highest single-character total profession level.
             *
             * @var array<string, string>
             *
             * @example {"1":"redacted","2":"72250a1d-144c-48d8-8223-1209ffcaf82d","3":"35f63806-4f34-4abb-99aa-ca54a5328696"}
             */
            'professionsSoloLevel' => $this->resource['professionsSoloLevel'] ?? [],
            /**
             * Top 10 players (by UUID) ranked by combined total character level (combat + professions) across all of their characters.
             *
             * @var array<string, string>
             *
             * @example {"1":"redacted","2":"7181f903-7849-49b4-9d5c-2f94ecb67313","3":"63fac5fc-806d-419a-b715-c1e7c60f3ebb"}
             */
            'totalGlobalLevel' => $this->resource['totalGlobalLevel'] ?? [],
            /**
             * Top 10 players (by UUID) ranked by highest single-character total level.
             *
             * @var array<string, string>
             *
             * @example {"1":"72250a1d-144c-48d8-8223-1209ffcaf82d","2":"1c8078de-f158-4e2a-a19e-82e653b04205","3":"redacted"}
             */
            'totalSoloLevel' => $this->resource['totalSoloLevel'] ?? [],
            /**
             * Top 10 players (by UUID) ranked by completed quest, discovery, and other content across their highest character.
             *
             * @var array<string, string>
             *
             * @example {"1":"72250a1d-144c-48d8-8223-1209ffcaf82d","2":"1c8078de-f158-4e2a-a19e-82e653b04205","3":"5c715fab-6a8d-4e00-bb19-9f64d136be68"}
             */
            'playerContent' => $this->resource['playerContent'] ?? [],
            /**
             * Top 10 players (by UUID) ranked by combined content completion across all of their characters.
             *
             * @var array<string, string>
             *
             * @example {"1":"72250a1d-144c-48d8-8223-1209ffcaf82d","2":"d34764bf-38bd-4317-b7d6-dd71cf94271b","3":"e95f089e-8d79-4a5d-858d-cebb1fdc5ee1"}
             */
            'globalPlayerContent' => $this->resource['globalPlayerContent'] ?? [],
            /**
             * Top 10 players (by UUID) by highest woodcutting profession level.
             *
             * @var array<string, string>
             */
            'woodcuttingLevel' => $this->resource['woodcuttingLevel'] ?? [],
            /**
             * Top 10 players (by UUID) by highest mining profession level.
             *
             * @var array<string, string>
             */
            'miningLevel' => $this->resource['miningLevel'] ?? [],
            /**
             * Top 10 players (by UUID) by highest fishing profession level.
             *
             * @var array<string, string>
             */
            'fishingLevel' => $this->resource['fishingLevel'] ?? [],
            /**
             * Top 10 players (by UUID) by highest farming profession level.
             *
             * @var array<string, string>
             */
            'farmingLevel' => $this->resource['farmingLevel'] ?? [],
            /**
             * Top 10 players (by UUID) by highest alchemism profession level.
             *
             * @var array<string, string>
             */
            'alchemismLevel' => $this->resource['alchemismLevel'] ?? [],
            /**
             * Top 10 players (by UUID) by highest armouring profession level.
             *
             * @var array<string, string>
             */
            'armouringLevel' => $this->resource['armouringLevel'] ?? [],
            /**
             * Top 10 players (by UUID) by highest cooking profession level.
             *
             * @var array<string, string>
             */
            'cookingLevel' => $this->resource['cookingLevel'] ?? [],
            /**
             * Top 10 players (by UUID) by highest jeweling profession level.
             *
             * @var array<string, string>
             */
            'jewelingLevel' => $this->resource['jewelingLevel'] ?? [],
            /**
             * Top 10 players (by UUID) by highest scribing profession level.
             *
             * @var array<string, string>
             */
            'scribingLevel' => $this->resource['scribingLevel'] ?? [],
            /**
             * Top 10 players (by UUID) by highest tailoring profession level.
             *
             * @var array<string, string>
             */
            'tailoringLevel' => $this->resource['tailoringLevel'] ?? [],
            /**
             * Top 10 players (by UUID) by highest weaponsmithing profession level.
             *
             * @var array<string, string>
             */
            'weaponsmithingLevel' => $this->resource['weaponsmithingLevel'] ?? [],
            /**
             * Top 10 players (by UUID) by highest woodworking profession level.
             *
             * @var array<string, string>
             */
            'woodworkingLevel' => $this->resource['woodworkingLevel'] ?? [],
            /**
             * Top 10 hunted-mode players (by UUID) ranked by completed content.
             *
             * @var array<string, string>
             */
            'huntedContent' => $this->resource['huntedContent'] ?? [],
            /**
             * Top 10 craftsman-mode players (by UUID) ranked by completed content.
             *
             * @var array<string, string>
             */
            'craftsmanContent' => $this->resource['craftsmanContent'] ?? [],
            /**
             * Top 10 ironman-mode players (by UUID) ranked by completed content.
             *
             * @var array<string, string>
             */
            'ironmanContent' => $this->resource['ironmanContent'] ?? [],
            /**
             * Top 10 ultimate-ironman-mode players (by UUID) ranked by completed content.
             *
             * @var array<string, string>
             */
            'ultimateIronmanContent' => $this->resource['ultimateIronmanContent'] ?? [],
            /**
             * Top 10 hardcore-mode players (by UUID) ranked by completed content.
             *
             * @var array<string, string>
             */
            'hardcoreContent' => $this->resource['hardcoreContent'] ?? [],
            /**
             * Top 10 hunted + ultimate-ironman + craftsman players (HUIC) ranked by completed content.
             *
             * @var array<string, string>
             */
            'huicContent' => $this->resource['huicContent'] ?? [],
            /**
             * Top 10 hunted + ultimate-ironman + craftsman + hardcore players (HUICH) ranked by completed content.
             *
             * @var array<string, string>
             */
            'huichContent' => $this->resource['huichContent'] ?? [],
            /**
             * Top 10 hunted + ironman + craftsman players (HIC) ranked by completed content.
             *
             * @var array<string, string>
             */
            'hicContent' => $this->resource['hicContent'] ?? [],
            /**
             * Top 10 hunted + ironman + craftsman + hardcore players (HICH) ranked by completed content.
             *
             * @var array<string, string>
             */
            'hichContent' => $this->resource['hichContent'] ?? [],
            /**
             * Top 10 legacy hardcore players (by UUID) ranked by highest character level.
             *
             * @var array<string, string>
             */
            'hardcoreLegacyLevel' => $this->resource['hardcoreLegacyLevel'] ?? [],
            /**
             * Top 10 players (by UUID) ranked by total guild wars cleared.
             *
             * @var array<string, string>
             */
            'warsCompletion' => $this->resource['warsCompletion'] ?? [],
            /**
             * Top 10 players (by UUID) ranked by Nest of the Grootslangs raid completions.
             *
             * @var array<string, string>
             */
            'grootslangCompletion' => $this->resource['grootslangCompletion'] ?? [],
            /**
             * Top 10 players (by UUID) ranked by The Canyon Colossus raid completions.
             *
             * @var array<string, string>
             */
            'colossusCompletion' => $this->resource['colossusCompletion'] ?? [],
            /**
             * Top 10 players (by UUID) ranked by Orphion's Nexus of Light raid completions.
             *
             * @var array<string, string>
             */
            'orphionCompletion' => $this->resource['orphionCompletion'] ?? [],
            /**
             * Top 10 players (by UUID) ranked by The Nameless Anomaly raid completions.
             *
             * @var array<string, string>
             */
            'namelessCompletion' => $this->resource['namelessCompletion'] ?? [],
            /**
             * Top 10 players (by UUID) ranked by Fallen in Fruma raid completions.
             *
             * @var array<string, string>
             */
            'frumaCompletion' => $this->resource['frumaCompletion'] ?? [],
            /**
             * Top 10 players (by UUID) by Nest of the Grootslangs solo speedrun time.
             *
             * @var array<string, string>
             */
            'grootslangSrPlayers' => $this->resource['grootslangSrPlayers'] ?? [],
            /**
             * Top 10 players (by UUID) by The Canyon Colossus solo speedrun time.
             *
             * @var array<string, string>
             */
            'colossusSrPlayers' => $this->resource['colossusSrPlayers'] ?? [],
            /**
             * Top 10 players (by UUID) by Orphion's Nexus of Light solo speedrun time.
             *
             * @var array<string, string>
             */
            'orphionSrPlayers' => $this->resource['orphionSrPlayers'] ?? [],
            /**
             * Top 10 players (by UUID) by The Nameless Anomaly solo speedrun time.
             *
             * @var array<string, string>
             */
            'namelessSrPlayers' => $this->resource['namelessSrPlayers'] ?? [],
            /**
             * Top 10 players (by UUID) by Fallen in Fruma solo speedrun time.
             *
             * @var array<string, string>
             */
            'frumaSrPlayers' => $this->resource['frumaSrPlayers'] ?? [],
            /**
             * Top 10 players (by UUID) by Nest of the Grootslangs group speedrun time.
             *
             * @var array<string, string>
             */
            'grootslangSrGPlayers' => $this->resource['grootslangSrGPlayers'] ?? [],
            /**
             * Top 10 players (by UUID) by The Canyon Colossus group speedrun time.
             *
             * @var array<string, string>
             */
            'colossusSrGPlayers' => $this->resource['colossusSrGPlayers'] ?? [],
            /**
             * Top 10 players (by UUID) by Orphion's Nexus of Light group speedrun time.
             *
             * @var array<string, string>
             */
            'orphionSrGPlayers' => $this->resource['orphionSrGPlayers'] ?? [],
            /**
             * Top 10 players (by UUID) by The Nameless Anomaly group speedrun time.
             *
             * @var array<string, string>
             */
            'namelessSrGPlayers' => $this->resource['namelessSrGPlayers'] ?? [],
            /**
             * Top 10 players (by UUID) by Fallen in Fruma group speedrun time.
             *
             * @var array<string, string>
             */
            'frumaSrGPlayers' => $this->resource['frumaSrGPlayers'] ?? [],
            /**
             * Top 10 guilds by Nest of the Grootslangs speedrun time.
             *
             * @var array<string, string>
             *
             * @example {"1":"Sequoia","2":"Aequitas","3":"Paladins United","4":"The Aquarium","5":"Avicia","6":"The Broken Gasmask","7":"Black Fangs","8":"Empire of TKW","9":"Eden","10":"Empire of Sindria"}
             */
            'grootslangSrGuilds' => $this->resource['grootslangSrGuilds'] ?? [],
            /**
             * Top 10 guilds by The Canyon Colossus speedrun time.
             *
             * @var array<string, string>
             *
             * @example {"1":"Sequoia","2":"The Broken Gasmask","3":"Titans Valor","4":"Avicia","5":"Aequitas","6":"Anime Lovers","7":"The Aquarium","8":"Nerfuria","9":"NexusRolly Love","10":"Paladins United"}
             */
            'colossusSrGuilds' => $this->resource['colossusSrGuilds'] ?? [],
            /**
             * Top 10 guilds by Orphion's Nexus of Light speedrun time.
             *
             * @var array<string, string>
             *
             * @example {"1":"Sequoia","2":"Hesperides","3":"Anime Lovers","4":"The Broken Gasmask","5":"Eden","6":"Titans Valor","7":"Aequitas","8":"Black Fangs","9":"Nerfuria","10":"Paladins United"}
             */
            'orphionSrGuilds' => $this->resource['orphionSrGuilds'] ?? [],
            /**
             * Top 10 guilds by The Nameless Anomaly speedrun time.
             *
             * @var array<string, string>
             *
             * @example {"1":"Sequoia","2":"Aequitas","3":"TruthSworD","4":"The Broken Gasmask","5":"Hesperides","6":"Eden","7":"Empire of TKW","8":"Anime Lovers","9":"Titans Valor","10":"Paladins United"}
             */
            'namelessSrGuilds' => $this->resource['namelessSrGuilds'] ?? [],
            /**
             * Top 10 guilds by Fallen in Fruma speedrun time.
             *
             * @var array<string, string>
             *
             * @example {"1":"Sequoia","2":"Aequitas","3":"Eden","4":"Empire of TKW","5":"Hesperides","6":"Idiot Co","7":"Titans Valor","8":"Avicia","9":"Imperial","10":"Nerfuria"}
             */
            'frumaSrGuilds' => $this->resource['frumaSrGuilds'] ?? [],
            /**
             * Top 10 guilds for guild war season 0.
             *
             * @var array<string, string>
             */
            'guildSeason0' => $this->resource['guildSeason0'] ?? [],
            /**
             * Top 10 guilds for guild war season 1.
             *
             * @var array<string, string>
             */
            'guildSeason1' => $this->resource['guildSeason1'] ?? [],
            /**
             * Top 10 guilds for guild war season 2.
             *
             * @var array<string, string>
             */
            'guildSeason2' => $this->resource['guildSeason2'] ?? [],
            /**
             * Top 10 guilds for guild war season 3.
             *
             * @var array<string, string>
             */
            'guildSeason3' => $this->resource['guildSeason3'] ?? [],
            /**
             * Top 10 guilds for guild war season 4.
             *
             * @var array<string, string>
             */
            'guildSeason4' => $this->resource['guildSeason4'] ?? [],
            /**
             * Top 10 guilds for guild war season 5.
             *
             * @var array<string, string>
             */
            'guildSeason5' => $this->resource['guildSeason5'] ?? [],
            /**
             * Top 10 guilds for guild war season 6.
             *
             * @var array<string, string>
             */
            'guildSeason6' => $this->resource['guildSeason6'] ?? [],
            /**
             * Top 10 guilds for guild war season 7.
             *
             * @var array<string, string>
             */
            'guildSeason7' => $this->resource['guildSeason7'] ?? [],
            /**
             * Top 10 guilds for guild war season 8.
             *
             * @var array<string, string>
             */
            'guildSeason8' => $this->resource['guildSeason8'] ?? [],
            /**
             * Top 10 guilds for guild war season 9.
             *
             * @var array<string, string>
             */
            'guildSeason9' => $this->resource['guildSeason9'] ?? [],
            /**
             * Top 10 guilds for guild war season 10.
             *
             * @var array<string, string>
             */
            'guildSeason10' => $this->resource['guildSeason10'] ?? [],
            /**
             * Top 10 guilds for guild war season 11.
             *
             * @var array<string, string>
             */
            'guildSeason11' => $this->resource['guildSeason11'] ?? [],
            /**
             * Top 10 guilds for guild war season 12.
             *
             * @var array<string, string>
             */
            'guildSeason12' => $this->resource['guildSeason12'] ?? [],
            /**
             * Top 10 guilds for guild war season 13.
             *
             * @var array<string, string>
             */
            'guildSeason13' => $this->resource['guildSeason13'] ?? [],
            /**
             * Top 10 guilds for guild war season 14.
             *
             * @var array<string, string>
             */
            'guildSeason14' => $this->resource['guildSeason14'] ?? [],
            /**
             * Top 10 guilds for guild war season 15.
             *
             * @var array<string, string>
             */
            'guildSeason15' => $this->resource['guildSeason15'] ?? [],
            /**
             * Top 10 guilds for guild war season 16.
             *
             * @var array<string, string>
             */
            'guildSeason16' => $this->resource['guildSeason16'] ?? [],
            /**
             * Top 10 guilds for guild war season 17.
             *
             * @var array<string, string>
             */
            'guildSeason17' => $this->resource['guildSeason17'] ?? [],
            /**
             * Top 10 guilds for guild war season 18.
             *
             * @var array<string, string>
             */
            'guildSeason18' => $this->resource['guildSeason18'] ?? [],
            /**
             * Top 10 guilds for guild war season 19.
             *
             * @var array<string, string>
             */
            'guildSeason19' => $this->resource['guildSeason19'] ?? [],
            /**
             * Top 10 guilds for guild war season 20.
             *
             * @var array<string, string>
             */
            'guildSeason20' => $this->resource['guildSeason20'] ?? [],
            /**
             * Top 10 guilds for guild war season 21.
             *
             * @var array<string, string>
             */
            'guildSeason21' => $this->resource['guildSeason21'] ?? [],
            /**
             * Top 10 guilds for guild war season 22.
             *
             * @var array<string, string>
             */
            'guildSeason22' => $this->resource['guildSeason22'] ?? [],
            /**
             * Top 10 guilds for guild war season 23.
             *
             * @var array<string, string>
             */
            'guildSeason23' => $this->resource['guildSeason23'] ?? [],
            /**
             * Top 10 guilds for guild war season 24.
             *
             * @var array<string, string>
             */
            'guildSeason24' => $this->resource['guildSeason24'] ?? [],
            /**
             * Top 10 guilds for guild war season 25.
             *
             * @var array<string, string>
             */
            'guildSeason25' => $this->resource['guildSeason25'] ?? [],
            /**
             * Top 10 guilds for guild war season 26.
             *
             * @var array<string, string>
             */
            'guildSeason26' => $this->resource['guildSeason26'] ?? [],
            /**
             * Top 10 guilds for guild war season 27.
             *
             * @var array<string, string>
             */
            'guildSeason27' => $this->resource['guildSeason27'] ?? [],
            /**
             * Top 10 guilds for guild war season 28.
             *
             * @var array<string, string>
             */
            'guildSeason28' => $this->resource['guildSeason28'] ?? [],
            /**
             * Top 10 guilds for guild war season 29.
             *
             * @var array<string, string>
             */
            'guildSeason29' => $this->resource['guildSeason29'] ?? [],
            /**
             * Top 10 guilds for guild war season 30.
             *
             * @var array<string, string>
             *
             * @example {"1":"Sequoia","2":"Aequitas","3":"Avicia","4":"Nerfuria","5":"Titans Valor","6":"Paladins United","7":"Cirrus","8":"Polish Hussars","9":"Adventure of the bear","10":"KongoBoys"}
             */
            'guildSeason30' => $this->resource['guildSeason30'] ?? [],
            /**
             * Top 10 guilds for guild war season 31.
             *
             * @var array<string, string>
             *
             * @example {"1":"Sequoia","2":"Aequitas","3":"Avicia","4":"Paladins United","5":"Titans Valor","6":"KongoBoys","7":"Polish Hussars","8":"Lunaris","9":"Chiefs Of Corkus","10":"Idiot Co"}
             */
            'guildSeason31' => $this->resource['guildSeason31'] ?? [],
        ];
    }
}
