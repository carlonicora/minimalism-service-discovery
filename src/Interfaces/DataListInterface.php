<?php
namespace CarloNicora\Minimalism\Services\Discovery\Interfaces;

interface DataListInterface
{
    /**
     * @param string $id
     * @return DataInterface|null
     */
    public function findChild(string $id): ?DataInterface;

    /**
     * @param DataInterface $data
     * @return void
     */
    public function add(DataInterface $data): void;

    /**
     * @param string $id
     * @return void
     */
    public function remove(string $id): void;

    /**
     * @return DataInterface[]
     */
    public function getElements(): array;

    /**
     * @param DataListInterface|DataInterface $data
     * @return bool
     */
    public function hasChanged(DataListInterface|DataInterface $data): bool;
}