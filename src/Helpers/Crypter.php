<?php
namespace CarloNicora\Minimalism\Services\Discovery\Helpers;

use CarloNicora\JsonApi\Document;
use CarloNicora\Minimalism\Services\Discovery\Discovery;
use Exception;

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
     * @return void
     * @throws Exception
     */
    public function preparePayload(
        Document $payload,
    ): void {
        $payload->meta->add(name: 'service', value: $this->discovery->getConfigurations()?->getService());
        $payload->meta->add(name: 'microservice', value: $this->discovery->getConfigurations()?->getName());
        $payload->meta->add(name: 'url', value: $this->discovery->getConfigurations()?->getUrl());
        $payload->meta->add(name: 'version', value: $this->discovery->getConfigurations()?->getVersion());
        $payload->meta->add(name: 'key', value: $this->discovery->getConfigurations()?->getPublicKey());
        $payload->meta->add(name: 'time', value: time());
    }

    /**
     * @param Document $payload
     * @return void
     * @throws Exception
     */
    public function signPayload(
        Document $payload,
    ): void {
        $unencryptedSignature = $this->getUnencryptedSignature($payload);

        $encrypted = '';
        openssl_public_encrypt($unencryptedSignature, $encrypted, $this->discovery->getConfigurations()?->getPrivateKey(), OPENSSL_PKCS1_OAEP_PADDING);
        $response = bin2hex($encrypted);

        $payload->meta->add(name: 'signature', value: $response);
    }

    /**
     * @param Document $payload
     * @return bool
     * @throws Exception
     */
    public function isSignatureValid(
        Document $payload,
    ): bool {
        $decryptedSignature = '';
        openssl_private_decrypt($payload->meta->get(name: 'signature'), $decryptedSignature, $this->discovery->getConfigurations()?->getPrivateKey(), OPENSSL_PKCS1_OAEP_PADDING);

        return $decryptedSignature === $this->getUnencryptedSignature($payload);
    }

    /**
     * @param Document $payload
     * @return string
     * @throws Exception
     */
    private function getUnencryptedSignature(
        Document $payload,
    ): string {
        $data = '';
        foreach ($payload->resources as $resource){
            $data .= json_encode($resource->prepare(), JSON_THROW_ON_ERROR);
        }

        return md5($data) .
            $payload->meta->get(name: 'service') .
            $payload->meta->get(name: 'microservice') .
            $payload->meta->get(name: 'url') .
            $payload->meta->get(name: 'version') .
            $payload->meta->get(name: 'key') .
            $payload->meta->get(name: 'time');
    }

    /**
     * @param string $privateKey
     * @param string $publicKey
     * @return void
     */
    public function generateNewKey(
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