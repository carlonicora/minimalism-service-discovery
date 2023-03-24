<?php
namespace CarloNicora\Minimalism\Services\Discovery\Data;

use CarloNicora\JsonApi\Objects\ResourceObject;
use CarloNicora\Minimalism\Services\Discovery\Data\Abstracts\AbstractDataList;
use CarloNicora\Minimalism\Services\Discovery\Data\enums\DataTypes;
use CarloNicora\Minimalism\Services\Discovery\Interfaces\DataInterface;
use CarloNicora\Minimalism\Services\Discovery\Interfaces\DataListInterface;
use Exception;

class MicroserviceData extends AbstractDataList
{
    private string $publicKey;
    private string $url;
    private string $version;
    private ?string $hostname=null;
    private ?string $docker=null;

    /**
     * @return DataTypes
     */
    public function getType(): DataTypes {
        return DataTypes::Microservice;
    }

    /**
     * @return ResourceObject
     * @throws Exception
     */
    public function export(): ResourceObject
    {
        $response = parent::export();

        $response->attributes->add(name: 'key', value: $this->publicKey);
        $response->attributes->add(name: 'url', value: $this->url);
        $response->attributes->add(name: 'version', value: $this->version);

        if ($this->hostname !== null) {
            $response->attributes->add(name: 'hostname', value: $this->hostname);
        }
        if ($this->docker !== null) {
            $response->attributes->add(name: 'docker', value: $this->docker);
        }

        return $response;
    }

    /**
     * @param ResourceObject $data
     * @return void
     * @throws Exception
     */
    public function import(ResourceObject $data): void
    {
        parent::import($data);

        $this->publicKey = $data->attributes->get(name: 'key');
        $this->url = $data->attributes->get(name: 'url');
        $this->version = $data->attributes->get(name: 'version');

        if ($data->attributes->has(name: 'hostname')){
            $this->hostname = $data->attributes->get(name: 'hostname');
        }

        if ($data->attributes->has(name: 'docker')){
            $this->docker = $data->attributes->get(name: 'docker');
        }
    }

    /**
     * @return string|null
     */
    public function getDocker(): ?string
    {
        return $this->docker;
    }

    /**
     * @param string|null $docker
     */
    public function setDocker(?string $docker): void
    {
        $this->docker = $docker;
    }

    /**
     * @return string
     */
    public function getPublicKey(): string
    {
        return $this->publicKey;
    }

    /**
     * @param string $publicKey
     */
    public function setPublicKey(string $publicKey): void
    {
        $this->publicKey = $publicKey;
    }

    /**
     * @return string|null
     */
    public function getHostname(): ?string
    {
        return $this->hostname;
    }

    /**
     * @param string|null $hostname
     */
    public function setHostname(?string $hostname): void
    {
        $this->hostname = $hostname;
    }

    /**
     * @return string
     */
    public function getUrl(): string
    {
        return $this->url;
    }

    /**
     * @param string $url
     */
    public function setUrl(string $url): void
    {
        $this->url = $url;
    }

    /**
     * @return string
     */
    public function getVersion(): string
    {
        return $this->version;
    }

    /**
     * @param string $version
     */
    public function setVersion(string $version): void
    {
        $this->version = $version;
    }

    /**
     * @return string
     */
    public function getDiscoveryKeepaliveUrl(
    ): string {
        return '/v' . $this->version . '/microservice-' . $this->id . '/discovery/keepalive';
    }

    /**
     * @param DataListInterface|DataInterface $data
     * @return bool
     */
    public function hasChanged(DataListInterface|DataInterface $data): bool {
        /** @var MicroserviceData $data */

        if ($this->url !== $data->getUrl()) {
            return true;
        }

        if ($this->version !== $data->getVersion()) {
            return true;
        }

        if ($this->hostname !== $data->getHostname()) {
            return true;
        }

        if ($this->docker !== $data->getDocker()) {
            return true;
        }

        parent::hasChanged($data);

        return false;
    }
}