<?php

namespace CarloNicora\Minimalism\Services\Discovery\Data\Abstracts;

use CarloNicora\JsonApi\Objects\ResourceObject;
use CarloNicora\Minimalism\Services\Discovery\Interfaces\DataInterface;
use CarloNicora\Minimalism\Services\Discovery\Interfaces\DataListInterface;
use Exception;
use SplObjectStorage;

abstract class AbstractDataList extends AbstractData implements DataListInterface
{
    /** @var DataInterface[]  */
    protected array $children = [];

    /**
     * @param string $id
     * @return DataInterface|null
     */
    public function findChild(string $id): ?DataInterface
    {
        foreach ($this->children as $child){
            if (strtolower($child->getId()) === strtolower($id)){
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
        $newChildren = $data->getElements();
        $currentChildren = $this->getElements();

        if (count($data->getElements()) !== count($this->children)){
            $this->children = $data->getElements();
            $response = true;
        } else {
            $currentChildrenSet = new SplObjectStorage();
            foreach ($currentChildren as $currentChild) {
                $currentChildrenSet->attach($currentChild);
            }

            foreach ($newChildren as $newChild) {
                if (!$currentChildrenSet->contains($newChild)) {
                    $this->add($newChild);
                    $response = true;
                } else {
                    $currentChildIndex = null;
                    $currentChild = null;
                    foreach ($currentChildren as $index => $currentChild) {
                        if ($currentChild === $newChild) {
                            $currentChildIndex = $index;
                            break;
                        }
                    }

                    if ($currentChildIndex !== null && $currentChild !== null && $currentChild->hasChanged($newChild)) {
                        $response = true;
                    }
                }
            }

            foreach ($currentChildren as $index => $currentChild) {
                if (!in_array($currentChild, $newChildren, true)) {
                    unset($currentChildren[$index]);
                    $response = true;
                }
            }
        }

        return $response;
    }
}