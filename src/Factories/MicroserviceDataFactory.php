<?php
namespace CarloNicora\Minimalism\Services\Discovery\Factories;

use CarloNicora\JsonApi\Document;
use CarloNicora\Minimalism\Services\Discovery\Data\EndpointData;
use CarloNicora\Minimalism\Services\Discovery\Data\MicroserviceData;
use CarloNicora\Minimalism\Services\Discovery\Data\ServiceData;
use CarloNicora\Minimalism\Services\Discovery\Factories\Abstracts\AbstractDataFactory;
use Exception;
use RuntimeException;

class MicroserviceDataFactory extends AbstractDataFactory
{

    /**
     * @return ServiceData[]
     * @throws Exception
     * @noinspection NullPointerExceptionInspection
     */
    public function read(
    ): array {
        $service = new ServiceData($this->discovery->getMicroserviceConfigurations()->getService());

        $microservice = new MicroserviceData($this->discovery->getMicroserviceConfigurations()->getName());
        $microservice->setPublicKey($this->discovery->getMicroserviceConfigurations()->getPublicKey());
        $microservice->setUrl($this->discovery->getMicroserviceConfigurations()->getUrl());
        $microservice->setVersion($this->discovery->getMicroserviceConfigurations()->getVersion());

        if ($this->discovery->getMicroserviceConfigurations()->getHostname() !== null){
            $microservice->setHostname($this->discovery->getMicroserviceConfigurations()->getHostname());
        }

        if ($this->discovery->getMicroserviceConfigurations()->getDocker() !== null){
            $microservice->setDocker($this->discovery->getMicroserviceConfigurations()->getDocker());
        }
        $service->add($microservice);

        $registryFile = $this->path->getRoot() . '/microserviceRegistry.json';

        if (!is_file($registryFile)){
            return [];
        }

        $endpointsData = file_get_contents($registryFile);

        $endpoints = new Document(json_decode($endpointsData, true, 512, JSON_THROW_ON_ERROR));
        foreach ($endpoints->resources as $endpointResource){
            $endpoint = new EndpointData();
            $endpoint->import($endpointResource);
            $microservice->add($endpoint);
        }

        return [$service];
    }

    /**
     * @param ServiceData[]| $data
     * @return void
     * @throws Exception
     */
    public function write(
        array $data,
    ): void {
        $document = new Document();

        /** @var MicroserviceData $microservice */
        $microservice = $data[0]->getElements()[0];

        foreach ($microservice->getElements() as $endpoint){
            $document->addResource($endpoint->export());
        }

        $registryFile = $this->path->getRoot() . '/microserviceRegistry.json';
        file_put_contents($registryFile, $document->export());
    }


    /**
     * @param ServiceData $data
     * @param string $publicKey
     * @return Document
     * @throws Exception
     */
    public function export(
        ServiceData $data,
        string $publicKey,
    ): Document {
        $response = new Document();

        $response->addResource($data->export());
        $this->sign($response, $publicKey);

        return $response;
    }
}