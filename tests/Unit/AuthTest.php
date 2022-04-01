<?php

namespace Tests\Unit;

use App\Http\Libraries\MinecraftFakeAuth;
use App\Http\Requests\AuthRequest;
use Tests\TestCase;

class AuthTest extends TestCase
{
    public function testPublicKeyReturnsCorrectValue(): void
    {
        $response = $this->call('GET', 'auth/getPublicKey');

        $response->assertOK();
        $response->assertJsonStructure(['publicKeyIn']);
    }

    public function testEncryptionResponseReturnsCorrectValue(): void
    {
        $data = [
            'username' => 'Scyu_',
            'key' => '6a8769526cc0e3cafa9e966ae815d448e746e192edf2ecdab48f6b91a440d0a099f4b190b43de14aa1a7ddaccf0bfa00274bf5168f31ca93b670c16fd1ce64382fc5b56922bdd3061a9efd29b04147836748b1bf8ded8a8e1855949e870f97008b584856d85d99c44d2471370a317960fb2289045d99ea6d7466ae24b6ccce766b640e7cb7975feb362f5cb58713067da2970a3615f992b7c3b3af0fdfdc77a90871a7cf5f1705b77a0261db4c33477d580079f34fcb3c4d509b8f97038bfdfa776602132fbb27c4b2e3b9991ed0a8dc6055d87c7d53bd16590d31644a40fa465441596086942a38e9ec56ba04ab4af4b1c5fd0d6db4205d20794569c46e4f41a8498d08b33a1fb75e856ef20a295d11c40b0cb20b798623fbe86056507616c6a5aff2c96b98deff739da40426dcb72081a72e6a0eff9c21ab9dd562ebcc192c0cdcf12e382ae738c1e4fa38271a4eb2b80b375f261ea399f08acfb1bfa146c4587bdc120dd54506d7aba62c27d120658759cd730aa7d89919011ac5e5e6fe4b',
            'version' => '1.10.5_-1'
        ];

        $this->partialMock(AuthRequest::class, function ($mock) use ($data) {
            $mock->shouldReceive('passes')->andReturn(true);
            $mock->shouldReceive('validated')->andReturnUsing(function ($argument) use ($data) {
                return $data[$argument];
            });
        });

        $this->mock(MinecraftFakeAuth::class, function ($mock) {
            $mock->shouldReceive('getGameProfile')->andReturn([
                'name' => 'Scyu_',
                'id' => '879be29a-bcca-43d6-978a-321a4241c392'
            ]);
        });

        $response = $this->post('auth/responseEncryption', $data);
        $response->assertOk();
        $response->assertJsonStructure(['message', 'authToken', 'configFiles', 'hashes']);
        $response->assertJsonFragment(['message' => 'Authentication code generated.']);
    }
}
