<?php
namespace CarloNicora\Minimalism\Services\Discovery\Models\Discovery;

use CarloNicora\JsonApi\Document;
use CarloNicora\Minimalism\Enums\HttpCode;
use CarloNicora\Minimalism\Services\Discovery\Factories\RegistryDataFactory;
use CarloNicora\Minimalism\Services\Discovery\Models\Abstracts\AbstractDiscoveryModel;
use Exception;

class Keepalive extends AbstractDiscoveryModel
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
        if (!$this->crypter->isSignatureValid($payload, new RegistryDataFactory($this->path, $this->discovery))){
            return HttpCode::Unauthorized;
        }
        
        $this->discovery->updateRegistry($payload);

        return HttpCode::Created;
    }
}