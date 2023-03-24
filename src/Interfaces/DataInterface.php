<?php
namespace CarloNicora\Minimalism\Services\Discovery\Interfaces;

use CarloNicora\JsonApi\Objects\ResourceObject;
use CarloNicora\Minimalism\Services\Discovery\Data\enums\DataTypes;

interface DataInterface
{
    /**
     * @param string|null $id
     */
    public function __construct(?string $id);

    /**
     * @return string
     */
    public function getId(): string;

    /**
     * @return ResourceObject
     */
    public function export(): ResourceObject;

    /**
     * @param ResourceObject $data
     * @return void
     */
    public function import(ResourceObject $data): void;

    /**
     * @return DataTypes
     */
    public function getType(): DataTypes;

    /**
     * @param DataListInterface|DataInterface $data
     * @return bool
     */
    public function hasChanged(DataListInterface|DataInterface $data): bool;
}