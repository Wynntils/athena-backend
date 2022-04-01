<?php

namespace Tests\Unit;

use Tests\TestCase;

class EasterEggTest extends TestCase
{
    public function testEasterEggCapeForAprilFools(): void
    {
        $this->travelTo('April 1');

        $response = $this->call('GET', 'user/getInfo/879be29a-bcca-43d6-978a-321a4241c392');
        $response->assertJsonPath('user.cosmetics.hasCape', true);
        $response->assertJsonPath('user.cosmetics.hasElytra', false);
    }
}
