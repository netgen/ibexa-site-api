<?php

declare(strict_types=1);

namespace Netgen\IbexaSiteApi\Core\Site\Pagination\Pagerfanta;

use Ibexa\Contracts\Core\Repository\Repository;
use Ibexa\Contracts\Core\Repository\Values\Content\LocationQuery;
use Ibexa\Contracts\Core\Repository\Values\Content\Query;
use Ibexa\Contracts\Core\Repository\Values\Content\Search\SearchResult;
use Netgen\IbexaSearchExtra\Core\Pagination\Pagerfanta\BaseAdapter;
use Netgen\IbexaSiteApi\API\FilterService;

/**
 * Pagerfanta adapter performing search using FilterService and Repository sudo.
 */
final class SudoFilterAdapter extends BaseAdapter
{
    public function __construct(
        Query $query,
        private readonly FilterService $filterService,
        private readonly Repository $repository,
    ) {
        parent::__construct($query);
    }

    protected function executeQuery(Query $query): SearchResult
    {
        if ($query instanceof LocationQuery) {
            return $this->repository->sudo(
                fn () => $this->filterService->filterLocations($query),
            );
        }

        return $this->repository->sudo(
            fn () => $this->filterService->filterContent($query),
        );
    }
}
