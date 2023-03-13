<?php
namespace CarloNicora\Minimalism\Services\Discovery\Models\Discovery;

use CarloNicora\JsonApi\Document;
use CarloNicora\Minimalism\Enums\HttpCode;
use CarloNicora\Minimalism\Services\Discovery\Models\Abstracts\AbstractDiscoveryModel;
use Exception;

class Register extends AbstractDiscoveryModel
{
    /**
     * @param Document $payload
     * @return HttpCode
     * @throws Exception
     */
    public function post(
        Document $payload,
    ): HttpCode
    {
        if (!$this->crypter->isSignatureValid($payload)){
            return HttpCode::Unauthorized;
        }

        return HttpCode::Created;
    }
}