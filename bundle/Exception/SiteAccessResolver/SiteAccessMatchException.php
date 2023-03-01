<?php

declare(strict_types=1);

namespace Netgen\Bundle\IbexaSiteApiBundle\Exception\SiteAccessResolver;

use Exception;
use Ibexa\Contracts\Core\Repository\Values\Content\Location;

use function sprintf;

final class SiteAccessMatchException extends Exception
{
    public static function locationNotMatched(Location $location): self
    {
        return new self(
            sprintf(
                'No siteaccess matched Location #%d',
                $location->id,
            ),
        );
    }
}
