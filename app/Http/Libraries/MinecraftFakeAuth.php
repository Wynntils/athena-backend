<?php

namespace App\Http\Libraries;

use App\Http\Traits\Singleton;
use OpenSSLAsymmetricKey;

class MinecraftFakeAuth
{
    use Singleton;

    private OpenSSLAsymmetricKey $privateKey;
    private OpenSSLAsymmetricKey $publicKey;

    protected function __construct()
    {
        $this->privateKey = openssl_pkey_get_private(\Storage::get('private.key'));
        $public_key_pem = openssl_pkey_get_details($this->privateKey)['key'];
        $this->publicKey = openssl_pkey_get_public($public_key_pem);
    }

    /**
     * @return string
     */
    public function getPublicKey(): string
    {
        $out = openssl_pkey_get_details($this->publicKey)['key'];
        $out = str_replace(['-----BEGIN PUBLIC KEY-----', '-----END PUBLIC KEY-----', "\n"], '', $out);
        return base64_decode($out);
    }

    /**
     * Verifies if the provided username and key were validated through Mojang
     *
     * @param string $username the username
     * @param string $key the client shared key generated based on our public key
     *
     * @return array|null the user GameProfile if authenticated, null otherwise
     */
    public function getGameProfile(string $username, string $key): ?array
    {
        $encrypted = hex2bin($key);

        openssl_private_decrypt($encrypted, $sharedKey, $this->privateKey);

        $serverId = $this->sha1($sharedKey . $this->getPublicKey());

        $url = sprintf(config('athena.api.mojang.auth'), $username, $serverId);

        return \Http::get($url)->json();
    }

    public function sha1($str): string
    {
        $gmp = gmp_import(sha1($str, true));
        if(gmp_cmp($gmp, gmp_init("0x8000000000000000000000000000000000000000")) >= 0)
        {
            $gmp = gmp_mul(gmp_add(gmp_xor($gmp, gmp_init("0xFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFF")), gmp_init(1)), gmp_init(-1));
        }
        return gmp_strval($gmp, 16);
    }
}
