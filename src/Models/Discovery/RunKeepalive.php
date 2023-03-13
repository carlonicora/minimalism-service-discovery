<?php
namespace CarloNicora\Minimalism\Services\Discovery\Models\Discovery;

use CarloNicora\Minimalism\Enums\HttpCode;
use CarloNicora\Minimalism\Services\Discovery\Models\Abstracts\AbstractDiscoveryModel;

class RunKeepalive extends AbstractDiscoveryModel
{
    public function cli(

    ): HttpCode {


        return HttpCode::Ok;
    }
}