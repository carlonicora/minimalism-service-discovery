<?php
namespace CarloNicora\Minimalism\Services\Discovery\Interfaces;

use CarloNicora\JsonApi\Document;

interface DataEncrypterInterface
{
    /**
     * @param Document $document
     * @param string $publicKey
     * @return void
     */
    public function sign(Document $document, string $publicKey): void;

    /**
     * @param Document $payload
     * @return string
     */
    public function getUnencryptedSignature(Document $payload): string;
}