<?php
namespace CarloNicora\Minimalism\Services\Discovery\Data\enums;

use CarloNicora\JsonApi\Objects\ResourceObject;
use CarloNicora\Minimalism\Services\Discovery\Data\EndpointData;
use CarloNicora\Minimalism\Services\Discovery\Data\MethodData;
use CarloNicora\Minimalism\Services\Discovery\Data\MicroserviceData;
use CarloNicora\Minimalism\Services\Discovery\Data\ServiceData;
use CarloNicora\Minimalism\Services\Discovery\Interfaces\DataInterface;
use Exception;
use RuntimeException;

enum DataTypes
{
    case Endpoint;
    case Method;
    case Microservice;
    case Service;

    /**
     * @return string
     */
    public function getName(): string {
        return match ($this) {
            self::Endpoint => 'endpoint',
            self::Method => 'method',
            self::Microservice => 'microservice',
            self::Service => 'service',
        };
    }

    public function getChildType(): DataTypes {
        return match($this) {
            self::Endpoint => self::Method,
            self::Microservice => self::Endpoint,
            self::Service => self::Microservice,
            default => throw new RuntimeException(''),
        };
    }

    /**
     * @return string
     */
    public function getChildName(): string {
        return match ($this){
            self::Endpoint => 'methods',
            self::Microservice => 'endpoints',
            self::Service => 'microservices',
            default => throw new RuntimeException(''),
        };
    }

    /**
     * @param string|null $id
     * @param ResourceObject|null $data
     * @return DataInterface
     * @throws Exception
     */
    public function create(?string $id=null, ?ResourceObject $data=null): DataInterface {
        $response = match ($this) {
            self::Endpoint => new EndpointData($id),
            self::Method => new MethodData($id),
            self::Microservice => new MicroserviceData($id),
            self::Service => new ServiceData($id),
        };

        if ($data !== null) {
            $response->import($data);
        }

        return $response;
    }
}