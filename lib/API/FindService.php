<?php

declare(strict_types=1);

namespace Netgen\IbexaSiteApi\API;

use Ibexa\Contracts\Core\Repository\Values\Content\LocationQuery;
use Ibexa\Contracts\Core\Repository\Values\Content\Query;
use Ibexa\Contracts\Core\Repository\Values\Content\Search\SearchResult;

/**
 * Find service provides methods for finding entities using Ibexa Repository Search Query API.
 *
 * Unlike FilterService, FindService uses search engine configured for the repository (Legacy or Solr).
 */
interface FindService
{
    /**
     * Finds Content objects for the given $query.
     *
     * @see \Netgen\IbexaSiteApi\API\Values\Content
     *
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException If $query is not valid
     */
    public function findContent(Query $query): SearchResult;

    /**
     * Finds Location objects for the given $query.
     *
     * @see \Netgen\IbexaSiteApi\API\Values\Location
     *
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException If $query is not valid
     */
    public function findLocations(LocationQuery $query): SearchResult;
}
