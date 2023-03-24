<?php
namespace CarloNicora\Minimalism\Services\Discovery\Helpers;

readonly class RegistryConfiguration
{
    public function __construct(
        private string $publicKey,
        private string $privateKey,
    )
    {
    }

    /**
     * @return string
     */
    public function getPublicKey(): string
    {
        return $this->publicKey;
    }

    /**
     * @return string
     */
    public function getPrivateKey(): string
    {
        return $this->privateKey;
    }
}