<?php

namespace App\Http\Enums;

enum MajorIdentifications
{
   case PLAGUE;
   case HAWKEYE;
   case GREED;
   case CAVALRYMAN;
   case GUARDIAN;
   case ALTRUISM;
   case HERO;
   case ARCANES;
   case ENTROPY;
   case ROVINGASSASSIN;
   case MAGNET;
   case MADNESS;
   case LIGHTWEIGHT;
   case SORCERY;
   case TAUNT;
   case FREERUNNER;
   case RALLY;
   case CHERRY_BOMBS;
   case PEACEFUL_EFFIGY;
   case FURIOUS_EFFIGY;
   case FLASHFREEZE;
   case FISSION;
   case EXPLOSIVE_IMPACT;
   case GEOCENTRISM;
   case GRAVITYWELL;
   case DESC_SNOWYSTEPS;
   case DESC_FESTIVESPIRIT;

   public function displayName(): string
   {
       return match ($this) {
           self::PLAGUE => 'Plague',
           self::HAWKEYE => 'Hawkeye',
           self::GREED => 'Greed',
           self::CAVALRYMAN => 'Cavalryman',
           self::GUARDIAN => 'Guardian',
           self::ALTRUISM => 'Heart of the Pack',
           self::HERO => 'Saviour\'s Sacrifice',
           self::ARCANES => 'Transcendence',
           self::ENTROPY => 'Entropy',
           self::ROVINGASSASSIN => 'Roving Assassin',
           self::MAGNET => 'Magnet',
           self::MADNESS => 'Madness',
           self::LIGHTWEIGHT => 'Lightweight',
           self::SORCERY => 'Sorcery',
           self::TAUNT => 'Taunt',
           self::FREERUNNER => 'Freerunner',
           self::RALLY => 'Rally',
           self::CHERRY_BOMBS => 'Cherry Bombs',
           self::PEACEFUL_EFFIGY => 'Peaceful Effigy',
           self::FURIOUS_EFFIGY => 'Furious Effigy',
           self::FLASHFREEZE => 'Flash Freeze',
           self::FISSION => 'Fission',
           self::EXPLOSIVE_IMPACT => 'Explosive Impact',
           self::GEOCENTRISM => 'Geocentrism',
           self::GRAVITYWELL => 'Gravity Well',
           self::DESC_SNOWYSTEPS => 'Snowy Steps',
           self::DESC_FESTIVESPIRIT => 'Festive Spirit',
       };
   }

   public function description(): string
   {
       return match ($this) {
           self::PLAGUE => 'Poisoned mobs spread their poison to nearby mobs',
           self::HAWKEYE => 'Condense Arrow Storm into a tight beam. Arrows deal ✣ 10%, ✦ 1%, and ❋ 1%',
           self::GREED => 'Picking up emeralds heals you and nearby players for 15% max health',
           self::CAVALRYMAN => 'You may cast spells and attack with a -70% damage penalty while on a horse',
           self::GUARDIAN => '20% of damage taken by nearby allies is redirected to you',
           self::ALTRUISM => 'Nearby players gain 35% of the health you naturally regenerate',
           self::HERO => 'While under 50% maximum health, nearby allies gain 30% bonus damage and defence',
           self::ARCANES => '30% chance for spells to cost no mana when casted',
           self::ENTROPY => 'Meteor falls three times faster',
           self::ROVINGASSASSIN => 'Vanish will no longer block mana and health regeneration',
           self::MAGNET => 'Pulls items dropped by mobs towards you',
           self::MADNESS => 'Casts a random spell of your class every 3 seconds',
           self::LIGHTWEIGHT => 'You no longer take fall damage',
           self::SORCERY => '30% chance for spells and attacks to cast a second time at no additional cost',
           self::TAUNT => 'Mobs within 12 blocks target you upon casting War Scream',
           self::FREERUNNER => 'When your sprint bar is under 30% full, increase your sprint speed by +150%',
           self::RALLY => 'Charge heals you by 10% and nearby allies by 15% on impact, but becomes harmless',
           self::CHERRY_BOMBS => 'Your Smoke Bombs explode instantly, and increases their Neutral Damage by +90%',
           self::PEACEFUL_EFFIGY => 'Your Totem will last twice as long',
           self::FURIOUS_EFFIGY => 'Totem effects are twice as fast, but duration is halved',
           self::FLASHFREEZE => 'Ice Snake is instant and freezes for +1s',
           self::FISSION => 'Explosions from your "Exploding" ID are twice as big and twice as strong',
           self::EXPLOSIVE_IMPACT => 'Your "Exploding" ID can trigger when hitting mobs with your Main Attack',
           self::GEOCENTRISM => 'Aura radiates from you instead of your Totem and can be cast any time',
           self::GRAVITYWELL => 'Meteor has increased blast radius and pulls enemies instead',
           self::DESC_SNOWYSTEPS => 'Leaves a trail of snow behind you',
           self::DESC_FESTIVESPIRIT => 'Plays wintery tunes',
       };
   }
}
