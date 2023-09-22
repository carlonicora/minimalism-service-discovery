<?php

namespace CarloNicora\Minimalism\Services\Discovery\Data\Abstracts;

use CarloNicora\JsonApi\Objects\ResourceObject;
use CarloNicora\Minimalism\Services\Discovery\Interfaces\DataInterface;
use CarloNicora\Minimalism\Services\Discovery\Interfaces\DataListInterface;
use Exception;

abstract class AbstractDataList extends AbstractData implements DataListInterface
{
    /** @var DataInterface[]  */
    protected array $children = [];

    /**
     * @param string $id
     * @return DataInterface|DataListInterface|null
     */
    public function findChild(string $id): DataInterface|DataListInterface|null
    {
        foreach ($this->children as $child){
            if (str_ends_with(strtolower($child->getId()), '-' . strtolower($id)) && substr_count($child->getId(), '-') === 2){
                return $child;
            }
        }

        return null;
    }

    /**
     * @return DataInterface[]
     */
    public function getElements(): array
    {
        return $this->children;
    }

    /**
     * @param DataInterface $data
     * @return void
     */
    public function add(DataInterface $data): void
    {
        $this->children[] = $data;
    }

    /**
     * @param string $id
     * @return void
     */
    public function remove(string $id): void
    {
        foreach ($this->children as $key => $child){
            if ($child->getId() === $id){
                array_splice($this->children, $key, 1);
                return;
            }
        }
    }

    /**
     * @return ResourceObject
     * @throws Exception
     */
    public function export(): ResourceObject
    {
        $response = parent::export();

        foreach ($this->children as $child){
            $response->relationship($this->getType()->getChildName())->resourceLinkage->addResource($child->export());
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

        foreach ($data->relationship($this->getType()->getChildName())->resourceLinkage->resources as $childData){
            $this->children[] = $this->getType()->getChildType()->create(null, $childData);
        }
    }

    /**
     * @param DataListInterface|DataInterface $data
     * @return bool
     */
    public function hasChanged(DataListInterface|DataInterface $data): bool {
        $response = false;

        if (count($data->getElements()) !== count($this->children)){
            $response = true;
        } else {
            foreach ($data->getElements() as $newChild) {
                foreach ($this->getElements() as $child){
                    if ($child->getId() === $newChild->getId() && $child->hasChanged($newChild)) {
                        $response = true;
                        break 2;
                    }
                }
            }
        }


        if ($response) {
            $this->children = $data->getElements();
        }

        return $response;
    }
}