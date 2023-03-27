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

        /** @var MicroserviceData $microservice */
        foreach ($data->getElements() as $microservice){
            $config .= "\n    location {$microservice->getDiscoveryKeepaliveUrl() } {\n";

            if ($microservice->getDocker() !== null) {
                $config .= "        resolver 127.0.0.11 valid=10s;\n";
                $config .= "        set \$upstream {$microservice->getDocker()};\n";
                $config .= "        proxy_pass http://\$upstream;\n";
            } elseif ($microservice->getHostname() !== null){
                $config .= "        proxy_pass {$microservice->getHostname()};\n";
            }

            $config .= "        include /etc/nginx/proxy.conf;\n";
            $config .= "        limit_except OPTIONS POST {deny all;}\n";
            $config .= "        proxy_set_header X-Original-URI \$request_uri;\n";
            $config .= "        rewrite ^/v{$microservice->getVersion()}/microservice-{$microservice->getId()}(.*)$ /v{$microservice->getVersion()}$1 break;\n";
            $config .= "    }\n";

            /** @var EndpointData $endpoint */
            foreach ($microservice->getElements() as $endpoint){
                $allowed = ' OPTIONS';
                foreach ($endpoint->getElements() as $method) {
                    $allowed .= ' ' . $method->getId();
                }

                $config .= "\n    location /v{$microservice->getVersion()}/{$this->convertEndpoint($endpoint->getName())} {\n";

                if ($microservice->getDocker() !== null) {
                    $config .= "        resolver 127.0.0.11 valid=10s;\n";
                    $config .= "        set \$upstream {$microservice->getDocker()};\n";
                    $config .= "        proxy_pass http://\$upstream;\n";
                } elseif ($microservice->getHostname() !== null){
                    $config .= "        proxy_pass {$microservice->getHostname()};\n";
                }

                $config .= "        include /etc/nginx/proxy.conf;\n";
                $config .= "        limit_except $allowed {deny all;}\n";
                $config .= "    }\n";
            }
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

        $parts = explode('/', $endpoint);
        $response = '';
        foreach ($parts as $part){
            if (str_starts_with($part, ':')){
                $response .= '/[^/]+';
            } else {
                $response .= '/' . $part;
            }
        }

        return $response;
    }
}