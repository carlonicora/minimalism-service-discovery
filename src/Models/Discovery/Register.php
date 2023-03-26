<?php
namespace CarloNicora\Minimalism\Services\Discovery\Models\Discovery;

use CarloNicora\JsonApi\Document;
use CarloNicora\Minimalism\Enums\HttpCode;
use CarloNicora\Minimalism\Services\Discovery\Data\MicroserviceData;
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

        $isServiceFound = false;
        $isUpdated = false;

        $newService = new ServiceData();
        $newService->import($payload->resources[0]);
        /** @var MicroserviceData $newMicroservice */
        $newMicroservice = $newService->getElements()[0];

        foreach ($registry as $registryService){
            if ($registryService->getId() === $newService->getId()){
                $isServiceFound = true;

                /** @var MicroserviceData $registryMicroservice */
                foreach ($registryService->getElements() as $registryMicroservice){
                    if ($registryMicroservice->getId() === $newMicroservice->getId()){
                        $isUpdated = $registryMicroservice->hasChanged($newMicroservice);
                        break 2;
                    }
                }

                $registryService->add($newMicroservice);
                $isUpdated = true;
                break;
            }
        }

        if (!$isServiceFound){
            $isUpdated = true;
            $registry[] = $newService;
        }

        if ($isUpdated){
            $this->discovery->setRegistry($registry);
            $this->discovery->getDataFactory()->write($registry);

            $this->discovery->updateNginxConfig();
        }

        return HttpCode::Created;
    }
}