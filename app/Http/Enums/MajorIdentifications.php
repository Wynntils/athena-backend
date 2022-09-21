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
       };
   }

   public function description(): string
   {
       return match ($this) {
           self::PLAGUE => 'Poisoned mobs spread their poison to nearby mobs',
           self::HAWKEYE => 'Arrow storm fires 5 arrows each dealing 80% damage',
           self::GREED => 'Picking up emeralds heals you and nearby players for 15% max health',
           self::CAVALRYMAN => 'You may cast spells and attack with a 70% damage penalty while on a horse',
           self::GUARDIAN => '50% of damage taken by nearby allies is redirected to you',
           self::ALTRUISM => 'Nearby players gain 35% of the health you naturally regenerate',
           self::HERO => 'While under 50% maximum health, nearby allies gain 50% bonus damage and defence',
           self::ARCANES => '50% chance for spells to cost no mana when casted',
           self::ENTROPY => 'Meteor falls three times faster',
           self::ROVINGASSASSIN => 'Vanish will no longer block mana and health regeneration',
           self::MAGNET => 'Pulls items within an 8 block radius towards you',
           self::MADNESS => 'Casts a random ability every 3 seconds',
           self::LIGHTWEIGHT => 'You no longer take fall damage',
           self::SORCERY => '30% chance for spells and attacks to cast a second time at no additional cost',
           self::TAUNT => 'Mobs within 12 blocks target you upon casting War Scream',
           self::FREERUNNER => 'Double your sprint speed when your sprint bar is under 30%',
           self::RALLY => 'Charge heals you by 10% and nearby allies by 15% on impact, but becomes harmless',
           self::CHERRY_BOMBS => 'Your Smoke Bombs explode instantly on contact, dealing 110% damage each',
           self::PEACEFUL_EFFIGY => 'Your Totem will last twice as long',
           self::FURIOUS_EFFIGY => 'Totem effects are twice as fast, but duration is halved',
           self::FLASHFREEZE => 'Ice Snake is instant but has a reduced range',
           self::FISSION => 'Explosions from your "Exploding" ID are twice as big and twice as strong',
           self::EXPLOSIVE_IMPACT => 'Your "Exploding" ID can trigger when hitting mobs with your basic attack',
           self::GEOCENTRISM => 'Aura radiates from you instead of your Totem and can be cast any time',
       };
   }
}
