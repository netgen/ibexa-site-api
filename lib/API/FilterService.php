<?php

declare(strict_types=1);

namespace Netgen\IbexaSiteApi\API;

use Ibexa\Contracts\Core\Repository\Values\Content\LocationQuery;
use Ibexa\Contracts\Core\Repository\Values\Content\Query;
use Ibexa\Contracts\Core\Repository\Values\Content\Search\SearchResult;

/**
 * Filters service provides methods for filters entities using
 * Ibexa Repository Search Query API.
 *
 * Unlike FindService, FilterService always uses Legacy search engine.
 */
interface FilterService
{
    /**
     * Filters Content objects for the given $query.
     *
     * @see \Netgen\IbexaSiteApi\API\Values\Content
     *
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException If $query is not valid
     */
    public function filterContent(Query $query): SearchResult;

    /**
     * Filters Location objects for the given $query.
     *
     * @see \Netgen\IbexaSiteApi\API\Values\Location
     *
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException If $query is not valid
     */
    public function filterLocations(LocationQuery $query): SearchResult;
}
