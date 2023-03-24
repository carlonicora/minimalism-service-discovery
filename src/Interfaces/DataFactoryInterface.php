<?php
namespace CarloNicora\Minimalism\Services\Discovery\Interfaces;

use CarloNicora\JsonApi\Document;
use CarloNicora\Minimalism\Services\Discovery\Data\ServiceData;

interface DataFactoryInterface
{
    /**
     * @return ServiceData[]
     */
    public function read(
    ): array;

    /**
     * @param ServiceData[]| $data
     * @return void
     */
    public function write(
        array $data,
    ): void;

    /**
     * @param ServiceData $data
     * @param string $publicKey
     * @return Document
     */
    public function export(
        ServiceData $data,
        string $publicKey,
    ): Document;
}