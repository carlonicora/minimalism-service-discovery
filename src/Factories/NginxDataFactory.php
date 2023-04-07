<?php
namespace CarloNicora\Minimalism\Services\Discovery\Factories;

use CarloNicora\JsonApi\Document;
use CarloNicora\Minimalism\Services\Discovery\Data\EndpointData;
use CarloNicora\Minimalism\Services\Discovery\Data\MicroserviceData;
use CarloNicora\Minimalism\Services\Discovery\Data\ServiceData;
use CarloNicora\Minimalism\Services\Discovery\Helpers\ServerConfigurations;
use CarloNicora\Minimalism\Services\Discovery\Interfaces\DataFactoryInterface;
use RuntimeException;

readonly class NginxDataFactory implements DataFactoryInterface
{
    /**
     * @param ServerConfigurations[] $serversConfig
     */
    public function __construct(
        private array $serversConfig,
    )
    {
    }

    /**
     * @return array
     */
    public function read(
    ): array
    {
        throw new RuntimeException('Not Implemented');
    }

    /**
     * @param array $data
     * @return void
     */
    public function write(
        array $data,
    ): void
    {
        foreach ($data as $service) {
            $this->writeSingleConfig($service);
        }
    }

    /**
     * @param ServiceData $data
     * @param string $publicKey
     * @return Document
     */
    public function export(
        ServiceData $data,
        string $publicKey,
    ): Document
    {
        throw new RuntimeException('Not Implemented');
    }

    /**
     * @param ServiceData $data
     * @return void
     */
    private function writeSingleConfig(
        ServiceData $data,
    ): void {
        $config = 'server {
    listen 80;
    server_name ' . $this->serversConfig[$data->getId()]->getUrl() . ';
    return 301 https://$host$request_uri;
}

server {
    listen 443 ssl;
    server_name ' . $this->serversConfig[$data->getId()]->getUrl() . ';

    ssl_certificate /etc/letsencrypt/live/' . $this->serversConfig[$data->getId()]->getSslKey() . '/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/' . $this->serversConfig[$data->getId()]->getSslKey() . '/privkey.pem;
';

        $keepAliveConfig = '';
        $priorityEndpointConfig = '';
        $endpointConfig = '';

        /** @var MicroserviceData $microservice */
        foreach ($data->getElements() as $microservice){
            $keepAliveConfig .= "\n    location {$microservice->getDiscoveryKeepaliveUrl() } {\n";

            if ($microservice->getDocker() !== null) {
                $keepAliveConfig .= "        resolver 127.0.0.11 valid=10s;\n";
                $keepAliveConfig .= "        set \$upstream {$microservice->getDocker()};\n";
                $keepAliveConfig .= "        proxy_pass http://\$upstream;\n";
            } elseif ($microservice->getHostname() !== null){
                $keepAliveConfig .= "        proxy_pass {$microservice->getHostname()};\n";
            }

            $keepAliveConfig .= "        include /etc/nginx/proxy.conf;\n";
            $keepAliveConfig .= "        limit_except OPTIONS POST {deny all;}\n";
            $keepAliveConfig .= "        proxy_set_header X-Original-URI \$request_uri;\n";
            $keepAliveConfig .= "        rewrite ^/v{$microservice->getVersion()}/microservice-{$microservice->getId()}(.*)$ /v{$microservice->getVersion()}$1 break;\n";
            $keepAliveConfig .= "    }\n";

            /** @var EndpointData $endpoint */
            foreach ($microservice->getElements() as $endpoint){
                $allowed = ' OPTIONS';
                foreach ($endpoint->getElements() as $method) {
                    $allowed .= ' ' . $method->getId();
                }

                $newConfig = "\n    location ~ /v" . str_replace('.', '\.', $microservice->getVersion()) . "/{$this->convertEndpoint($endpoint->getName())} {\n";

                if ($microservice->getDocker() !== null) {
                    $newConfig .= "        resolver 127.0.0.11 valid=10s;\n";
                    $newConfig .= "        set \$upstream {$microservice->getDocker()};\n";
                    $newConfig .= "        proxy_pass http://\$upstream;\n";
                } elseif ($microservice->getHostname() !== null){
                    $newConfig .= "        proxy_pass {$microservice->getHostname()};\n";
                }

                $newConfig .= "        include /etc/nginx/proxy.conf;\n";
                $newConfig .= "        limit_except $allowed {deny all;}\n";
                $newConfig .= "    }\n";

                if (str_contains($endpoint->getName(), ':')){
                    $priorityEndpointConfig .= $newConfig;
                } else {
                    $endpointConfig .= $newConfig;
                }
            }
        }

        if ($keepAliveConfig !== ''){
            $config .= "\n    ## Keep Alive Configurations";
            $config .= $keepAliveConfig;
        }

        if ($priorityEndpointConfig !== '') {
            $config .= "\n    ## One to Many endpoints (prioritised)";
            $config .= $priorityEndpointConfig;
        }

        if ($endpointConfig !== '') {
            $config .= "\n    ## Endpoints";
            $config .= $endpointConfig;
        }

        $config .= '}';

        file_put_contents('/etc/nginx/sites-available/' . $data->getId() . '.conf', $config);
    }

    private function convertEndpoint(
        string $endpoint,
    ): string {
        if (!str_contains($endpoint, '/')){
            return $endpoint;
        }

        $edited = false;

        $parts = explode('/', $endpoint);
        $response = '';
        foreach ($parts as $part){
            if (str_starts_with($part, ':')){
                $response .= '/[^/]+';
                $edited = true;
            } else {
                $response .= '/' . $part;
            }
        }

        if (str_starts_with($response, '/')){
            $response = substr($response, 1);
        }

        if ($edited){
            $response = str_replace('.','\.',$response);
        }

        return $response;
    }
}