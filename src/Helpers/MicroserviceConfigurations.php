<?php
namespace CarloNicora\Minimalism\Services\Discovery\Helpers;

readonly class MicroserviceConfigurations
{
    public function __construct(
        private string $name,
        private string $service,
        private string $url,
        private string $version,
        private string $publicKey,
        private ?string $privateKey=null,
    )
    {
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getPrivateKey(): string
    {
        return $this->privateKey;
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
    public function getService(): string
    {
        return $this->service;
    }

    /**
     * @return string
     */
    public function getUrl(): string
    {
        return $this->url;
    }

    /**
     * @return string
     */
    public function getVersion(): string
    {
        return $this->version;
    }
}