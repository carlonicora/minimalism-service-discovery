<?php
namespace CarloNicora\Minimalism\Services\Discovery\Helpers;

readonly class ServerConfigurations
{
    public function __construct(
        private string $url,
        private string $publicKey,
        private ?string $hostName=null,
    )
    {
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    /**
     * @return string
     */
    public function getPublicKey(): string
    {
        return $this->publicKey;
    }

    /**
     * @return string|null
     */
    public function getHostName(): ?string
    {
        return $this->hostName;
    }
}