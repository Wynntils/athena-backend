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
        return bin2hex(openssl_pkey_get_details($this->publicKey)['rsa']['n']);
    }

    /**
     * Verifies if the provided username and key were validated through Mojang
     *
     * @param string $username the username
     * @param string $key the client shared key generated based on our public key
     *
     * @return null the user GameProfile if authenticated, null otherwise
     */
    public function getGameProfile(string $username, string $key) {
        $encrypted = hex2bin($key); // val encrypted = DatatypeConverter.parseHexBinary(key) // converts the string to a ByteArray

        $sharedKey = CryptManager::decryptSharedKey($this->privateKey, $encrypted);

        $verificationKey = $this->getServerHash($sharedKey);

        $url = sprintf('https://sessionserver.mojang.com/session/minecraft/hasJoined?username=%s&serverId=%s', $username, $verificationKey);

        return \Http::get($url)->json();
        /*
            val sharedKey = CryptManager.decryptSharedKey(
                keyPair.private,
                encrypted
            ) // decrypts the client sent shared key using our private key

            val verificationKey =
                BigInteger(getServerHash(sharedKey)).toString(16) // converts the server hash to string
            val url = apiConfig.mojangAuth.format(username, verificationKey)

            val connection = URL(url).openConnection() as HttpsURLConnection // open connection to Mojang server api

            val result = connection.inputStream.toPlainString()
            connection.inputStream.close()
            if (!result.contains("{")) return null // just a simple verification to check if it's a valid json
         */
    }

    private function getServerHash($key) {
        $serverId = "";
        $digest = openssl_digest(implode("", [$serverId, $key, $this->publicKey]), 'sha1');
        dd($digest);

        /*
        val serverId = "".toByteArray(StandardCharsets.ISO_8859_1)

        return Hash.SHA1.digest(arrayOf(serverId, key.encoded, keyPair.public.encoded))
         */
    }
}
