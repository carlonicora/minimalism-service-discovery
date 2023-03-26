<?php
namespace CarloNicora\Minimalism\Services\Discovery\Data\Abstracts;

use CarloNicora\JsonApi\Objects\ResourceObject;
use CarloNicora\Minimalism\Services\Discovery\Data\enums\DataTypes;
use CarloNicora\Minimalism\Services\Discovery\Interfaces\DataInterface;
use CarloNicora\Minimalism\Services\Discovery\Interfaces\DataListInterface;
use Exception;
use RuntimeException;

abstract class AbstractData implements DataInterface
{
    /** @var string */
    protected string $id;

     /**
     * @return DataTypes
     */
    abstract public function getType(): DataTypes;

    /**
     * @param string|null $id
     */
    public function __construct(
        ?string $id=null,
    )
    {
        if ($id !== null){
            $this->id = $id;
        }
    }

    /**
     * @return string
     */
    public function getId(): string {
        return $this->id;
    }

    /**
     * @return ResourceObject
     * @throws Exception
     */
    public function export(): ResourceObject
    {
        return new ResourceObject(type: $this->getType()->getName(), id: $this->id);
    }

    /**
     * @param ResourceObject $data
     * @return void
     */
    public function import(ResourceObject $data): void
    {
        if ($data->type !== $this->getType()->getName()){
            throw new RuntimeException('Invalid type');
        }

        $this->id = $data->id;
    }

    /**
     * @param DataListInterface|DataInterface $data
     * @return bool
     */
    public function hasChanged(DataListInterface|DataInterface $data): bool {
        $response = false;

        if($this->id !== $data->getId()){
            $this->id = $data->getId();
            $response = true;
        }

        return $response;
    }
}