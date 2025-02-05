<?php


namespace Tests\Feature;

use Illuminate\Support\Str;
use Tests\TestCase;
use Tests\Unit\AssertableJson;

class VersionControllerTest extends TestCase
{
    /**
     * Tests if the version controller returns the latest version for the right minecraft version
     *
     * @return void
     */
    public function test_is_right_minecraft_version(): void
    {

        $userAgent = "Wynntils Artemis\\v2.4.10-beta.71+MC-1.21.4 (client) FABRIC";
        $mcVersion = Str::after($userAgent, '+MC-');
        $mcVersion = Str::before($mcVersion, ' ');

        $response = $this->withHeaders([
            'User-Agent' => $userAgent,
        ])->getJson('/version/latest/release');

        $response->assertJson([
            'supportedMcVersion' => $mcVersion,
            ]);
    }
}
