<?php

declare(strict_types=1);

namespace Netgen\IbexaSiteApi\Core\Site\Routing;

use Netgen\IbexaSiteApi\API\Routing\UrlGenerator as APIUrlGenerator;
use RuntimeException;

/**
 * Default UrlGenerator implementation throws an exception as there is no integration with MVC on this level.
 */
class UrlGenerator extends APIUrlGenerator
{
    public function generate(
        object $object,
        string $siteaccess = null,
        int $referenceType = APIUrlGenerator::ABSOLUTE_PATH
    ): string {
        throw new RuntimeException(
            'Intentionally not implemented: implement this method in your MVC integration layer'
        );
    }
}
