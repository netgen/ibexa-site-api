<?php

declare(strict_types=1);

namespace Netgen\Bundle\IbexaSiteApiBundle\Routing;

use Ibexa\Contracts\Core\Repository\Values\Content\Location;

/**
 * Siteaccess resolver resolves a siteaccess for the given Location.
 */
abstract class CrossSiteaccessResolver
{
    /**
     * Resolve a siteaccess for the given Location.
     */
    abstract public function resolve(Location $location): string;
}
