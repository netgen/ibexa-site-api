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
    private FindService $findService;

    public function __construct(Query $query, FindService $findService)
    {
        parent::__construct($query);

        $this->findService = $findService;
    }

    /**
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException
     */
    protected function executeQuery(Query $query): SearchResult
    {
        if ($query instanceof LocationQuery) {
            return $this->findService->findLocations($query);
        }

        return $this->findService->findContent($query);
    }
}
