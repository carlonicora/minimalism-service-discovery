<?php
namespace CarloNicora\Minimalism\Services\Discovery\Factories;

use CarloNicora\JsonApi\Document;
use CarloNicora\Minimalism\Services\Discovery\Data\ServiceData;
use CarloNicora\Minimalism\Services\Discovery\Factories\Abstracts\AbstractDataFactory;
use Exception;

class RegistryDataFactory extends AbstractDataFactory
{
    /**
     * @return ServiceData[]
     * @throws Exception
     */
    public function read(): array
    {
        $response = [];

        $registryFile = $this->path->getRoot() . '/registry.json';

        if (is_file($registryFile)) {
            $servicesData = file_get_contents($registryFile);
            $document = new Document(json_decode($servicesData, true, 512, JSON_THROW_ON_ERROR));

            foreach ($document->resources as $serviceResource) {
                $serviceData = new ServiceData();
                $serviceData->import($serviceResource);
                $response[] = $serviceData;
            }
        }

        return $response;
    }

    /**
     * @param array $data
     * @return void
     * @throws Exception
     */
    public function write(
        array $data,
    ): void
    {
        $document = new Document();
        foreach ($data as $service) {
            $document->addResource($service->export());
        }

        $registryFile = $this->path->getRoot() . '/registry.json';
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
    ): Document
    {
        $response = new Document();
        $response->addResource($data->export());

        $this->sign($response, $publicKey);

        return $response;
    }
}