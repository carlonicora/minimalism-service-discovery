<?php
namespace CarloNicora\Minimalism\Services\Discovery;

use CarloNicora\Minimalism\Abstracts\AbstractService;
use CarloNicora\Minimalism\Services\Discovery\Helpers\MicroserviceConfigurations;
use CarloNicora\Minimalism\Services\Discovery\Helpers\ServerConfigurations;

class Discovery extends AbstractService
{
    private ?ServerConfigurations $proxy=null;
    private ?ServerConfigurations $loadBalancer=null;
    private ?MicroserviceConfigurations $configurations=null;

    public function __construct(
        readonly string $MINIMALISM_SERVICE_DISCOVERY_PUBLIC_KEY,
        readonly ?string $MINIMALISM_SERVICE_DISCOVERY_PRIVATE_KEY=null,

        readonly ?string $MINIMALISM_SERVICE_DISCOVERY_PROXY_URL=null,
        readonly ?string $MINIMALISM_SERVICE_DISCOVERY_PROXY_HOSTNAME=null,
        readonly ?string $MINIMALISM_SERVICE_DISCOVERY_PROXY_KEY=null,

        readonly ?string $MINIMALISM_SERVICE_DISCOVERY_LOADBALANCER_URL=null,
        readonly ?string $MINIMALISM_SERVICE_DISCOVERY_LOADBALANCER_HOSTNAME=null,
        readonly ?string $MINIMALISM_SERVICE_DISCOVERY_LOADBALANCER_KEY=null,

        readonly ?string $MINIMALISM_SERVICE_DISCOVERY_SERVICE=null,
        readonly ?string $MINIMALISM_SERVICE_DISCOVERY_MICROSERVICE=null,
        readonly ?string $MINIMALISM_SERVICE_DISCOVERY_URL=null,
        readonly ?string $MINIMALISM_SERVICE_DISCOVERY_VERSION=null,
    )
    {
        if ($MINIMALISM_SERVICE_DISCOVERY_MICROSERVICE !== null) {
            $this->configurations = new MicroserviceConfigurations(
                $MINIMALISM_SERVICE_DISCOVERY_MICROSERVICE,
                $MINIMALISM_SERVICE_DISCOVERY_SERVICE,
                $MINIMALISM_SERVICE_DISCOVERY_URL,
                $MINIMALISM_SERVICE_DISCOVERY_VERSION,
                $MINIMALISM_SERVICE_DISCOVERY_PUBLIC_KEY,
                $MINIMALISM_SERVICE_DISCOVERY_PRIVATE_KEY,
            );
        }

        if ($MINIMALISM_SERVICE_DISCOVERY_PROXY_URL !== null){
            $this->proxy = new ServerConfigurations(
                $MINIMALISM_SERVICE_DISCOVERY_PROXY_URL,
                $MINIMALISM_SERVICE_DISCOVERY_PROXY_KEY,
                $MINIMALISM_SERVICE_DISCOVERY_PROXY_HOSTNAME,
            );
        }

        if ($MINIMALISM_SERVICE_DISCOVERY_LOADBALANCER_URL !== null){
            $this->loadBalancer = new ServerConfigurations(
                $MINIMALISM_SERVICE_DISCOVERY_LOADBALANCER_URL,
                $MINIMALISM_SERVICE_DISCOVERY_LOADBALANCER_KEY,
                $MINIMALISM_SERVICE_DISCOVERY_LOADBALANCER_HOSTNAME,
            );
        }
    }

    /**
     * @return MicroserviceConfigurations|null
     */
    public function getConfigurations(): ?MicroserviceConfigurations
    {
        return $this->configurations;
    }

    /**
     * @return ServerConfigurations|null
     */
    public function getProxy(): ?ServerConfigurations
    {
        return $this->proxy;
    }

    /**
     * @return ServerConfigurations|null
     */
    public function getLoadBalancer(): ?ServerConfigurations
    {
        return $this->loadBalancer;
    }
}