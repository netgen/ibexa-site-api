<?php

declare(strict_types=1);

namespace Netgen\Bundle\IbexaSiteApiBundle\QueryType;

use Ibexa\Contracts\Core\Repository\Repository;
use Ibexa\Contracts\Core\Repository\Values\Content\LocationQuery;
use Ibexa\Contracts\Core\Repository\Values\Content\Query;
use Ibexa\Contracts\Core\Repository\Values\Content\Search\SearchResult;
use Ibexa\Core\QueryType\QueryTypeRegistry;
use Netgen\IbexaSearchExtra\Core\Pagination\Pagerfanta\BaseAdapter;
use Netgen\IbexaSiteApi\API\FilterService;
use Netgen\IbexaSiteApi\API\FindService;
use Netgen\IbexaSiteApi\Core\Site\Pagination\Pagerfanta\FilterAdapter;
use Netgen\IbexaSiteApi\Core\Site\Pagination\Pagerfanta\FindAdapter;
use Netgen\IbexaSiteApi\Core\Site\Pagination\Pagerfanta\SudoFilterAdapter;
use Netgen\IbexaSiteApi\Core\Site\Pagination\Pagerfanta\SudoFindAdapter;
use Pagerfanta\Pagerfanta;

/**
 * QueryExecutor resolves the Query from the QueryDefinition, executes it and returns the result.
 *
 * @internal do not depend on this service, it can be changed without warning
 */
final class QueryExecutor
{
    public function __construct(
        private readonly QueryTypeRegistry $queryTypeRegistry,
        private readonly FilterService $filterService,
        private readonly FindService $findService,
        private readonly Repository $repository,
    ) {}

    /**
     * Execute the Query with the given $name and return the result.
     */
    public function execute(QueryDefinition $queryDefinition): Pagerfanta
    {
        $adapter = $this->getPagerAdapter($queryDefinition);
        $pager = new Pagerfanta($adapter);

        $pager->setNormalizeOutOfRangePages(true);
        $pager->setMaxPerPage($queryDefinition->maxPerPage);
        $pager->setCurrentPage($queryDefinition->page);

        return $pager;
    }

    /**
     * Execute the Query with the given $name and return the result using repository sudo.
     */
    public function sudoExecute(QueryDefinition $queryDefinition): Pagerfanta
    {
        $adapter = $this->getSudoPagerAdapter($queryDefinition);
        $pager = new Pagerfanta($adapter);

        $pager->setNormalizeOutOfRangePages(true);
        $pager->setMaxPerPage($queryDefinition->maxPerPage);
        $pager->setCurrentPage($queryDefinition->page);

        return $pager;
    }

    /**
     * Execute the Query with the given $name and return the result.
     */
    public function executeRaw(QueryDefinition $queryDefinition): SearchResult
    {
        $query = $this->getQuery($queryDefinition);

        if ($query instanceof LocationQuery) {
            return $this->getLocationResult($query, $queryDefinition);
        }

        return $this->getContentResult($query, $queryDefinition);
    }

    /**
     * Execute the Query with the given $name and return the result using repository sudo.
     */
    public function sudoExecuteRaw(QueryDefinition $queryDefinition): SearchResult
    {
        $query = $this->getQuery($queryDefinition);

        if ($query instanceof LocationQuery) {
            return $this->repository->sudo(
                fn () => $this->getLocationResult($query, $queryDefinition),
            );
        }

        return $this->repository->sudo(
            fn () => $this->getContentResult($query, $queryDefinition),
        );
    }

    private function getPagerAdapter(QueryDefinition $queryDefinition): BaseAdapter
    {
        $query = $this->getQuery($queryDefinition);

        if ($queryDefinition->useFilter) {
            return new FilterAdapter($query, $this->filterService);
        }

        return new FindAdapter($query, $this->findService);
    }

    private function getSudoPagerAdapter(QueryDefinition $queryDefinition): BaseAdapter
    {
        $query = $this->getQuery($queryDefinition);

        if ($queryDefinition->useFilter) {
            return new SudoFilterAdapter($query, $this->filterService, $this->repository);
        }

        return new SudoFindAdapter($query, $this->findService, $this->repository);
    }

    private function getLocationResult(LocationQuery $query, QueryDefinition $queryDefinition): SearchResult
    {
        if ($queryDefinition->useFilter) {
            return $this->filterService->filterLocations($query);
        }

        return $this->findService->findLocations($query);
    }

    private function getContentResult(Query $query, QueryDefinition $queryDefinition): SearchResult
    {
        if ($queryDefinition->useFilter) {
            return $this->filterService->filterContent($query);
        }

        return $this->findService->findContent($query);
    }

    private function getQuery(QueryDefinition $queryDefinition): Query
    {
        return $this->queryTypeRegistry->getQueryType($queryDefinition->name)->getQuery($queryDefinition->parameters);
    }
}
