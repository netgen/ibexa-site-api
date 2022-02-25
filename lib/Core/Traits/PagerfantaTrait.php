<?php

declare(strict_types=1);

namespace Netgen\IbexaSiteApi\Core\Traits;

use Ibexa\Contracts\Core\Repository\Values\Content\Query;
use Netgen\IbexaSiteApi\API\Site;
use Netgen\IbexaSiteApi\Core\Site\Pagination\Pagerfanta\FilterAdapter;
use Netgen\IbexaSiteApi\Core\Site\Pagination\Pagerfanta\FindAdapter;
use Pagerfanta\Pagerfanta;

/**
 * Provides methods to build Pagerfanta instance using on FilterAdapter or FindAdapter.
 */
trait PagerfantaTrait
{
    abstract protected function getSite(): Site;

    /**
     * Return Pagerfanta instance using FilterAdapter for the given $query.
     */
    protected function getFilterPager(Query $query, int $currentPage, int $maxPerPage): Pagerfanta
    {
        $adapter = new FilterAdapter($query, $this->getSite()->getFilterService());
        $pager = new Pagerfanta($adapter);

        $pager->setNormalizeOutOfRangePages(true);
        $pager->setMaxPerPage($maxPerPage);
        $pager->setCurrentPage($currentPage);

        return $pager;
    }

    /**
     * Return Pagerfanta instance using FindAdapter for the given $query.
     */
    protected function getFindPager(Query $query, int $currentPage, int $maxPerPage): Pagerfanta
    {
        $adapter = new FindAdapter($query, $this->getSite()->getFindService());
        $pager = new Pagerfanta($adapter);

        $pager->setNormalizeOutOfRangePages(true);
        $pager->setMaxPerPage($maxPerPage);
        $pager->setCurrentPage($currentPage);

        return $pager;
    }
}
