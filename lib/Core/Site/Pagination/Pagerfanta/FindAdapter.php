<?php

declare(strict_types=1);

namespace Netgen\IbexaSiteApi\Core\Site\Pagination\Pagerfanta;

use Ibexa\Contracts\Core\Repository\Values\Content\LocationQuery;
use Ibexa\Contracts\Core\Repository\Values\Content\Query;
use Ibexa\Contracts\Core\Repository\Values\Content\Search\SearchResult;
use Netgen\IbexaSearchExtra\Core\Pagination\Pagerfanta\BaseAdapter;
use Netgen\IbexaSiteApi\API\FindService;

/**
 * Pagerfanta adapter performing search using FindService.
 *
 * @see \Netgen\IbexaSiteApi\API\FindService
 */
final class FindAdapter extends BaseAdapter
{
    public function __construct(
        Query $query,
        private readonly FindService $findService,
    ) {
        parent::__construct($query);
    }

    protected function executeQuery(Query $query): SearchResult
    {
        if ($query instanceof LocationQuery) {
            return $this->findService->findLocations($query);
        }

        return $this->findService->findContent($query);
    }
}
