<?php

declare(strict_types=1);

namespace Netgen\IbexaSiteApi\Core\Site\QueryType;

use Ibexa\Core\QueryType\QueryType as BaseQueryTypeInterface;

/**
 * Extend the base QueryType interface with detection for a single supported parameter.
 */
interface QueryType extends BaseQueryTypeInterface
{
    /**
     * Check if the QueryType supports parameter with the given $name.
     */
    public function supportsParameter(string $name): bool;
}
