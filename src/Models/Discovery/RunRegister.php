<?php
namespace CarloNicora\Minimalism\Services\Discovery\Models\Discovery;

use CarloNicora\Minimalism\ApiCaller\ApiCaller;
use CarloNicora\Minimalism\ApiCaller\Data\ApiRequest;
use CarloNicora\Minimalism\ApiCaller\Enums\Verbs;
use CarloNicora\Minimalism\Enums\HttpCode;
use CarloNicora\Minimalism\Services\Discovery\Models\Abstracts\AbstractDiscoveryModel;
use Exception;

class RunRegister extends AbstractDiscoveryModel
{
    /**
     * @throws Exception
     */
    public function cli(
    ): HttpCode {
        $proxy = $this->discovery->getProxyConfigurations();
        if ($proxy === null){
            return HttpCode::InternalServerError;
        }

        $document = $this->discovery->getMicroserviceDataFactory()->export(
            $this->discovery->getMicroserviceRegistry()[0],
            $proxy->getPublicKey()
        );

        /** @noinspection UnusedFunctionResultInspection */
        (new ApiCaller())->call(
            request: new ApiRequest(
                verb: Verbs::Post,
                endpoint: 'discovery/register',
                payload: $document->prepare()
            ),
            serverUrl: $proxy->getUrl(),
            hostName: $proxy->getHostName(),
        );

        return HttpCode::Ok;
    }
}