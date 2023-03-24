<?php
namespace CarloNicora\Minimalism\Services\Discovery\Models\Abstracts;

use CarloNicora\Minimalism\Abstracts\AbstractModel;
use CarloNicora\Minimalism\Factories\MinimalismFactories;
use CarloNicora\Minimalism\Services\Discovery\Discovery;
use CarloNicora\Minimalism\Services\Discovery\Helpers\Crypter;
use CarloNicora\Minimalism\Services\Path;

class AbstractDiscoveryModel extends AbstractModel
{
    /** @var Discovery  */
    protected Discovery $discovery;

    /** @var Path  */
    protected Path $path;

    /** @var Crypter  */
    protected Crypter $crypter;

    public function __construct(
        MinimalismFactories $minimalismFactories,
        ?string $function = null,
    )
    {
        parent::__construct($minimalismFactories, $function);

        $this->path = $minimalismFactories->getServiceFactory()->getPath();
        $this->discovery = $minimalismFactories->getServiceFactory()->create(Discovery::class);

        $this->crypter = new Crypter($this->discovery);
    }
}