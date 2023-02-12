<?php

declare(strict_types=1);

namespace Netgen\Bundle\IbexaSiteApiBundle\SiteAccess;

use Ibexa\Contracts\Core\Repository\Values\Content\ContentInfo;
use Ibexa\Contracts\Core\Repository\Values\Content\Location;

/**
 * Siteaccess resolver resolves a siteaccess for the given Location.
 */
abstract class Resolver
{
    /**
     * Resolve a siteaccess from the given Location.
     */
    abstract public function resolveByLocation(Location $location): string;

    /**
     * Resolve a siteaccess from the given Content.
     */
    abstract public function resolveByContent(ContentInfo $contentInfo): string;
}
