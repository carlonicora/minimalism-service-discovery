<?php
namespace CarloNicora\Minimalism\Services\Discovery\Data;

use CarloNicora\Minimalism\Services\Discovery\Data\Abstracts\AbstractDataList;
use CarloNicora\Minimalism\Services\Discovery\Data\enums\DataTypes;
use CarloNicora\Minimalism\Services\Discovery\Interfaces\DataInterface;
use CarloNicora\Minimalism\Services\Discovery\Interfaces\DataListInterface;

class ServiceData extends AbstractDataList
{
    /**
     * @param string $id
     * @return DataInterface|DataListInterface|null
     */
    public function findChild(string $id): DataInterface|DataListInterface|null
    {
        foreach ($this->children as $child){
            if (strtolower($child->getId()) === strtolower($id)){
                return $child;
            }
        }

        return null;
    }
    /**
     * @return DataTypes
     */
    public function getType(): DataTypes {
        return DataTypes::Service;
    }
}