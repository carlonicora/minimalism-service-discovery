<?php
namespace CarloNicora\Minimalism\Services\Discovery;

use CarloNicora\JsonApi\Document;
use CarloNicora\Minimalism\Abstracts\AbstractService;
use CarloNicora\Minimalism\ApiCaller\ApiCaller;
use CarloNicora\Minimalism\ApiCaller\Data\ApiRequest;
use CarloNicora\Minimalism\ApiCaller\Enums\Verbs;
use CarloNicora\Minimalism\Services\Discovery\Data\EndpointData;
use CarloNicora\Minimalism\Services\Discovery\Data\MicroserviceData;
use CarloNicora\Minimalism\Services\Discovery\Data\ServiceData;
use CarloNicora\Minimalism\Services\Discovery\Factories\MicroserviceDataFactory;
use CarloNicora\Minimalism\Services\Discovery\Factories\NginxDataFactory;
use CarloNicora\Minimalism\Services\Discovery\Factories\RegistryDataFactory;
use CarloNicora\Minimalism\Services\Discovery\Helpers\MicroserviceConfigurations;
use CarloNicora\Minimalism\Services\Discovery\Helpers\ProxyConfigurations;
use CarloNicora\Minimalism\Services\Discovery\Helpers\RegistryConfiguration;
use CarloNicora\Minimalism\Services\Discovery\Helpers\ServerConfigurations;
use CarloNicora\Minimalism\Services\Discovery\Interfaces\DataFactoryInterface;
use CarloNicora\Minimalism\Services\Path;
use Exception;
use RuntimeException;

class Discovery extends AbstractService
{
    /** @var ProxyConfigurations|null  */
    private ?ProxyConfigurations $proxyConfigurations=null;
    /** @var MicroserviceConfigurations|null  */
    private ?MicroserviceConfigurations $microserviceConfigurations=null;
    /** @var RegistryConfiguration|null  */
    private ?RegistryConfiguration $registryConfigurations=null;

    /** @var ServerConfigurations[] */
    private array $serversConfig=[];

    /** @var ServiceData[]|null  */
    private ?array $registry=null;

    /** @var ServiceData[]|null  */
    private ?array $microserviceRegistry=null;

    /** @var DataFactoryInterface  */
    private DataFactoryInterface $dataFactory;
    private DataFactoryInterface $microserviceDataFactory;

    /** @var bool  */
    private bool $requiresKeepalive=false;

    public function __construct(
        readonly Path $path,
        private readonly string $MINIMALISM_SERVICE_DISCOVERY_PUBLIC_KEY,
        private readonly string $MINIMALISM_SERVICE_DISCOVERY_PRIVATE_KEY,

        readonly ?string $MINIMALISM_SERVICE_DISCOVERY_PROXY_URL=null,
        readonly ?string $MINIMALISM_SERVICE_DISCOVERY_PROXY_HOSTNAME=null,
        readonly ?string $MINIMALISM_SERVICE_DISCOVERY_PROXY_KEY=null,
        readonly ?string $MINIMALISM_SERVICE_DISCOVERY_PROXY_SERVERS=null,

        readonly ?string $MINIMALISM_SERVICE_DISCOVERY_LOADBALANCER_URL=null,
        readonly ?string $MINIMALISM_SERVICE_DISCOVERY_LOADBALANCER_HOSTNAME=null,
        readonly ?string $MINIMALISM_SERVICE_DISCOVERY_LOADBALANCER_KEY=null,

        readonly ?string $MINIMALISM_SERVICE_DISCOVERY_SERVICE=null,
        readonly ?string $MINIMALISM_SERVICE_DISCOVERY_MICROSERVICE=null,
        readonly ?string $MINIMALISM_SERVICE_DISCOVERY_URL=null,
        readonly ?string $MINIMALISM_SERVICE_DISCOVERY_VERSION=null,
        readonly ?string $MINIMALISM_SERVICE_DISCOVERY_HOSTNAME=null,
        readonly ?string $MINIMALISM_SERVICE_DISCOVERY_DOCKER=null,
    )
    {
        if ($MINIMALISM_SERVICE_DISCOVERY_MICROSERVICE !== null) {
            $this->microserviceConfigurations = new MicroserviceConfigurations(
                $MINIMALISM_SERVICE_DISCOVERY_MICROSERVICE,
                $MINIMALISM_SERVICE_DISCOVERY_SERVICE,
                $MINIMALISM_SERVICE_DISCOVERY_URL,
                $MINIMALISM_SERVICE_DISCOVERY_VERSION,
                $this->MINIMALISM_SERVICE_DISCOVERY_PUBLIC_KEY,
                $this->MINIMALISM_SERVICE_DISCOVERY_PRIVATE_KEY,
                $MINIMALISM_SERVICE_DISCOVERY_HOSTNAME,
                $MINIMALISM_SERVICE_DISCOVERY_DOCKER,
            );

            $this->proxyConfigurations = new ProxyConfigurations(
                $MINIMALISM_SERVICE_DISCOVERY_PROXY_URL,
                $MINIMALISM_SERVICE_DISCOVERY_PROXY_KEY,
                $MINIMALISM_SERVICE_DISCOVERY_PROXY_HOSTNAME,
            );
        } else {
            $this->registryConfigurations = new RegistryConfiguration(
                $this->MINIMALISM_SERVICE_DISCOVERY_PUBLIC_KEY,
                $this->MINIMALISM_SERVICE_DISCOVERY_PRIVATE_KEY,
            );

            foreach(explode(';',$MINIMALISM_SERVICE_DISCOVERY_PROXY_SERVERS) as $serversConfig){
                [$name, $config] = explode(':', $serversConfig);
                $this->serversConfig[$name] = new ServerConfigurations($config);
            }
        }
    }

    /**
     * @return void
     * @throws Exception
     */
    public function initialise(
    ): void
    {
        parent::initialise();

        $this->requiresKeepalive = false;

        if ($this->microserviceConfigurations !== null){
            $this->microserviceDataFactory = new MicroserviceDataFactory($this->path, $this);
            $this->microserviceRegistry = $this->microserviceDataFactory->read();
        }

        $this->dataFactory = new RegistryDataFactory($this->path, $this);
        $this->registry = $this->dataFactory->read();
    }

    /**
     * @return void
     * @throws Exception
     */
    public function destroy(): void
    {
        if ($this->requiresKeepalive){
            /** @var ServiceData $service */
            foreach ($this->registry as $service){
                /** @var MicroserviceData $microservice */
                foreach ($service->getElements() as $microservice) {
                    $document = $this->dataFactory->export($service, $microservice->getPublicKey());

                    $this->sendKeepalive(
                        $document,
                        $microservice->getDiscoveryKeepaliveUrl(),
                        $microservice->getHostname() ?? $microservice->getUrl(),
                    );
                }
            }

            $this->requiresKeepalive = false;
        }

        parent::destroy();
    }

    /**
     * @return string
     */
    public function getPublicKey(): string {
        return $this->MINIMALISM_SERVICE_DISCOVERY_PUBLIC_KEY;
    }

    /**
     * @return string
     */
    public function getPrivateKey(): string {
        return $this->MINIMALISM_SERVICE_DISCOVERY_PRIVATE_KEY;
    }

    /**
     * @param Document $payload
     * @param string $endpoint
     * @param string $hostname
     * @return void
     * @throws Exception
     */
    private function sendKeepalive(
        Document $payload,
        string $endpoint,
        string $hostname,
    ): void {
        /** @noinspection UnusedFunctionResultInspection */
        (new ApiCaller())->call(
            request: new ApiRequest(
                verb: Verbs::Post,
                endpoint: $endpoint,
                payload: $payload->prepare()
            ),
            serverUrl: 'https://host.docker.internal',
            hostName: $hostname,
        );
    }

    /**
     * @return MicroserviceConfigurations|null
     */
    public function getMicroserviceConfigurations(): ?MicroserviceConfigurations
    {
        return $this->microserviceConfigurations;
    }

    /**
     * @return ProxyConfigurations|null
     */
    public function getProxyConfigurations(): ?ProxyConfigurations
    {
        return $this->proxyConfigurations;
    }

    /**
     * @return RegistryConfiguration|null
     */
    public function getRegistryConfigurations(): ?RegistryConfiguration
    {
        return $this->registryConfigurations;
    }

    /**
     * @return ServiceData[]
     * @throws Exception
     */
    public function getRegistry(
    ): array
    {
        return $this->registry;
    }

    /**
     * @return array|null
     */
    public function getMicroserviceRegistry(): ?array
    {
        return $this->microserviceRegistry;
    }

    /**
     * @param array|null $registry
     */
    public function setRegistry(
        ?array $registry
    ): void
    {
        $this->registry = $registry;
    }

    /**
     * @return DataFactoryInterface
     */
    public function getDataFactory(): DataFactoryInterface
    {
        return $this->dataFactory;
    }

    /**
     * @return DataFactoryInterface
     */
    public function getMicroserviceDataFactory(): DataFactoryInterface
    {
        return $this->microserviceDataFactory;
    }

    /**
     * @param Document $document
     * @return void
     * @throws Exception
     */
    public function updateRegistry(
        Document $document,
    ): void {
        $this->registry = [];
        
        foreach ($document->resources as $serviceResource){
            $service = new ServiceData($serviceResource->id);
            $service->import($serviceResource);
            $this->registry[] = $service;
        }
        
        $this->dataFactory->write($this->registry);
    }

    /**
     * @param string $endpointId
     * @param string $url
     * @param string|null $hostname
     * @param string $method
     * @return string
     */
    public function getEndpointUrl(
        string $endpointId,
        string &$url,
        ?string &$hostname,
        string $method = 'GET',
    ): string {
        foreach ($this->registry as $registryService){
            if ($registryService->getId() === $this->microserviceRegistry[0]->getId()){
                /** @var MicroserviceData $microservice */
                foreach ($registryService->getElements() as $microservice) {
                    /** @var EndpointData $endpoint */
                    $endpoint = $microservice->findChild($endpointId);

                    if ($endpoint !== null && $endpoint->findChild($method) !== null){
                        $url = $microservice->getUrl();
                        $hostname = $microservice->getHostname();
                        return $endpoint->getId();
                    }
                }
            }
        }

        throw new RuntimeException('Endpoint not found');
    }

    /**
     * @return void
     */
    public function updateNginxConfig(): void {
        $nginxDataFactory = new NginxDataFactory($this->serversConfig);
        $nginxDataFactory->write($this->registry);

        $this->requiresKeepalive = true;
    }
}