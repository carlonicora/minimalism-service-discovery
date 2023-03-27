<?php
namespace CarloNicora\Minimalism\Services\Discovery\Data;

use CarloNicora\JsonApi\Objects\ResourceObject;
use CarloNicora\Minimalism\Services\Discovery\Data\Abstracts\AbstractDataList;
use CarloNicora\Minimalism\Services\Discovery\Data\enums\DataTypes;
use CarloNicora\Minimalism\Services\Discovery\Interfaces\DataInterface;
use CarloNicora\Minimalism\Services\Discovery\Interfaces\DataListInterface;
use Exception;

class EndpointData extends AbstractDataList
{
    private string $name;

    /**
     * @return DataTypes
     */
    public function getType(): DataTypes {
        return DataTypes::Endpoint;
    }

    /**
     * @return ResourceObject
     * @throws Exception
     */
    public function export(): ResourceObject
    {
        $response = parent::export();

        $response->attributes->add(name: 'name', value: $this->name);

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

        $this->name = $data->attributes->get(name: 'name');
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @param DataListInterface|DataInterface $data
     * @return bool
     */
    public function hasChanged(DataListInterface|DataInterface $data): bool {
        $response = false;
        /** @var MicroserviceData $data */

        if ($this->name !== $data->getName()) {
            $this->name = $data->getName();
            $response = true;
        }

        return $response || parent::hasChanged($data);
    }
}