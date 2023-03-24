<?php
namespace CarloNicora\Minimalism\Services\Discovery\Helpers;

readonly class ServerConfigurations
{
    /** @var string  */
    private string $url;
    /** @var string  */
    private string $sslKey;

    public function __construct(
        string $configs,
    )
    {
        [$this->url, $this->sslKey] = explode(',', $configs);
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
    public function getSslKey(): string
    {
        return $this->sslKey;
    }
}