<?php
namespace CarloNicora\Minimalism\Services\Discovery\Models\Discovery;

use CarloNicora\JsonApi\Document;
use CarloNicora\Minimalism\Enums\HttpCode;
use CarloNicora\Minimalism\Services\Discovery\Data\ServiceData;
use CarloNicora\Minimalism\Services\Discovery\Factories\MicroserviceDataFactory;
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
        if (!$this->crypter->isSignatureValid($payload, new MicroserviceDataFactory($this->path, $this->discovery))){
            return HttpCode::Unauthorized;
        }

        $registry = $this->discovery->getRegistry();

        /** @var ServiceData $service */
        $service = null;
        $isUpdated = false;

        foreach ($registry as $serviceKey => $registryService){
            if ($registryService->getId() === $payload->resources[0]->id){
                $service = new ServiceData();
                $service->import($payload->resources[0]);

                $isUpdated = $registryService->hasChanged($service);

                if ($isUpdated) {
                    $registry[$serviceKey] = $registryService;
                }
                break;
            }
        }

        if ($service === null){
            $isUpdated = true;
            $service = new ServiceData();
            $service->import($payload->resources[0]);
            $registry[] = $service;
        }

        if ($isUpdated){
            $this->discovery->setRegistry($registry);
            $this->discovery->getDataFactory()->write($registry);

            $this->discovery->updateNginxConfig();
        }

        return HttpCode::Created;
    }
}