<?php
namespace CarloNicora\Minimalism\Services\Discovery\Helpers;

use CarloNicora\JsonApi\Document;
use CarloNicora\Minimalism\Services\Discovery\Discovery;
use CarloNicora\Minimalism\Services\Discovery\Interfaces\DataEncrypterInterface;
use Exception;
use RuntimeException;

readonly class Crypter
{
    /**
     * @param Discovery $discovery
     */
    public function __construct(
        private Discovery $discovery,
    )
    {
    }

    /**
     * @param Document $payload
     * @param string $publicKey
     * @param DataEncrypterInterface $dataEncrypter
     * @return void
     * @throws Exception
     */
    public function signPayload(
        Document $payload,
        string $publicKey,
        DataEncrypterInterface $dataEncrypter,
    ): void {
        $unencryptedSignature = $dataEncrypter->getUnencryptedSignature($payload);
        openssl_public_encrypt($unencryptedSignature, $encrypted, $publicKey, OPENSSL_PKCS1_OAEP_PADDING);
        $payload->meta->add(name: 'signature', value: bin2hex($encrypted));
    }

    /**
     * @param Document $payload
     * @param DataEncrypterInterface $dataEncrypter
     * @return bool
     * @throws Exception
     */
    public function isSignatureValid(
        Document $payload,
        DataEncrypterInterface $dataEncrypter,
    ): bool {
        if ($payload->meta->get(name: 'time') < time() - 60) {
            throw new RuntimeException('Signature Expired');
        }

        $decryptedSignature = '';
        $signature = hex2bin($payload->meta->get(name: 'signature'));
        openssl_private_decrypt($signature, $decryptedSignature, $this->discovery->getPrivateKey(), OPENSSL_PKCS1_OAEP_PADDING);

        return $decryptedSignature === $dataEncrypter->getUnencryptedSignature($payload);
    }

    /**
     * @param string $privateKey
     * @param string $publicKey
     * @return void
     */
    public static function generateNewKey(
        string &$privateKey,
        string &$publicKey,
    ): void {
        $config = array(
            "digest_alg" => "sha512",
            "private_key_bits" => 4096,
            "private_key_type" => OPENSSL_KEYTYPE_RSA,
        );

        $res = openssl_pkey_new($config);
        openssl_pkey_export($res, $privateKey);

        $public_data = openssl_pkey_get_details($res);
        $publicKey = $public_data["key"];
    }
}