<?php

declare(strict_types=1);

namespace Netgen\Bundle\IbexaSiteApiBundle\View;

use Netgen\IbexaSiteApi\API\Values\Content;
use Netgen\IbexaSiteApi\API\Values\Location;

/**
 * Provides Location for the Content View when it's not explicitly given.
 */
abstract class LocationResolver
{
    /**
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException
     */
    abstract public function getLocation(Content $content): Location;
}
