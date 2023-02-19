<?php

declare(strict_types=1);

namespace Netgen\IbexaSiteApi\Core\Site\Pagination\Pagerfanta;

use Ibexa\Contracts\Core\Repository\Values\Content\LocationQuery;
use Ibexa\Contracts\Core\Repository\Values\Content\Query;
use Ibexa\Contracts\Core\Repository\Values\Content\Search\SearchResult;
use Netgen\IbexaSearchExtra\Core\Pagination\Pagerfanta\BaseAdapter;
use Netgen\IbexaSiteApi\API\FilterService;

/**
 * Pagerfanta adapter performing search using FilterService.
 *
 * @see \Netgen\IbexaSiteApi\API\FilterService
 */
final class FilterAdapter extends BaseAdapter
{
    public function __construct(
        Query $query,
        private readonly FilterService $filterService
    ) {
        parent::__construct($query);
    }

    protected function executeQuery(Query $query): SearchResult
    {
        if ($query instanceof LocationQuery) {
            return $this->filterService->filterLocations($query);
        }

        return $this->filterService->filterContent($query);
    }
}
