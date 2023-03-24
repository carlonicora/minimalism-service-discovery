<?php
namespace CarloNicora\Minimalism\Services\Discovery\Data;

use CarloNicora\Minimalism\Services\Discovery\Data\Abstracts\AbstractData;
use CarloNicora\Minimalism\Services\Discovery\Data\enums\DataTypes;

class MethodData extends AbstractData
{
    /**
     * @return DataTypes
     */
    public function getType(): DataTypes {
        return DataTypes::Method;
    }
}