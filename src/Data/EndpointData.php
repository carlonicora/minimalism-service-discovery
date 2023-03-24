<?php
namespace CarloNicora\Minimalism\Services\Discovery\Data;

use CarloNicora\Minimalism\Services\Discovery\Data\Abstracts\AbstractDataList;
use CarloNicora\Minimalism\Services\Discovery\Data\enums\DataTypes;

class EndpointData extends AbstractDataList
{
    /**
     * @return DataTypes
     */
    public function getType(): DataTypes {
        return DataTypes::Endpoint;
    }
}