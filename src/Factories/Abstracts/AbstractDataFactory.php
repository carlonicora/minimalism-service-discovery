<?php
namespace CarloNicora\Minimalism\Services\Discovery\Factories\Abstracts;

use CarloNicora\JsonApi\Document;
use CarloNicora\Minimalism\Services\Discovery\Data\ServiceData;
use CarloNicora\Minimalism\Services\Discovery\Discovery;
use CarloNicora\Minimalism\Services\Discovery\Helpers\Crypter;
use CarloNicora\Minimalism\Services\Discovery\Interfaces\DataEncrypterInterface;
use CarloNicora\Minimalism\Services\Discovery\Interfaces\DataFactoryInterface;
use CarloNicora\Minimalism\Services\Path;
use Exception;

abstract class AbstractDataFactory implements DataFactoryInterface, DataEncrypterInterface
{
    /**
     * @param Path $path
     * @param Discovery $discovery
     */
    public function __construct(
        protected Path      $path,
        protected Discovery $discovery,
    )
    {
    }

    /**
     * @return array|ServiceData[]
     */
    abstract public function read(): array;

    /**
     * @param ServiceData[]| $data
     * @return void
     * @throws Exception
     */
    abstract public function write(array $data): void;

    /**
     * @param Document $document
     * @param string $publicKey
     * @return void
     * @throws Exception
     */
    public function sign(Document $document, string $publicKey): void {
        $document->meta->add(name: 'time', value: time());

        $crypter = new Crypter($this->discovery);
        $crypter->signPayload($document, $publicKey, $this);
    }

    /**
     * @param Document $payload
     * @return string
     * @throws Exception
     */
    public function getUnencryptedSignature(
        Document $payload,
    ): string {
        $data = '';
        foreach ($payload->resources as $resource){
            $data .= json_encode($resource->prepare(), JSON_THROW_ON_ERROR);
        }

        return md5(
            $data .
            $payload->meta->get(name: 'time')
        );
    }
}