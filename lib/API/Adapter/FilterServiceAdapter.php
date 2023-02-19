<?php

declare(strict_types=1);

namespace Netgen\IbexaSiteApi\API\Adapter;

use Ibexa\Contracts\Core\Repository\SearchService;
use Ibexa\Contracts\Core\Repository\Values\Content\Content;
use Ibexa\Contracts\Core\Repository\Values\Content\LocationQuery;
use Ibexa\Contracts\Core\Repository\Values\Content\Query;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion;
use Ibexa\Contracts\Core\Repository\Values\Content\Search\SearchResult;
use Ibexa\Contracts\Core\Search\Capable;
use Ibexa\Contracts\Core\Search\Handler;
use Ibexa\Core\Base\Exceptions\InvalidArgumentException;
use Ibexa\Core\Base\Exceptions\NotFoundException;
use Netgen\IbexaSiteApi\API\FilterService;

/**
 * This class is an adapter from Site API filter service to SearchService interface
 * from Ibexa core. The point is being able to replace usage of Ibexa CMS SearchService
 * with Site API filter service without touching consuming code.
 *
 * Methods implemented here do not use $languageFilter argument since it is handled automatically
 * by the filter service itself.
 *
 * As for $filterOnUserPermissions, filter service doesn't support it, so it is simply ignored.
 */
final class FilterServiceAdapter implements SearchService
{
    public function __construct(
        private readonly FilterService $filterService,
        private readonly Handler $searchHandler,
    ) {
    }

    public function findContent(Query $query, array $languageFilter = [], bool $filterOnUserPermissions = true): SearchResult
    {
        $searchResult = $this->filterService->filterContent($query);

        foreach ($searchResult->searchHits as $searchHit) {
            /** @var \Netgen\IbexaSiteApi\API\Values\Content $siteContent */
            $siteContent = $searchHit->valueObject;
            $searchHit->valueObject = $siteContent->innerContent;
        }

        return $searchResult;
    }

    public function findContentInfo(Query $query, array $languageFilter = [], bool $filterOnUserPermissions = true): SearchResult
    {
        $searchResult = $this->filterService->filterContent($query);

        foreach ($searchResult->searchHits as $searchHit) {
            /** @var \Netgen\IbexaSiteApi\API\Values\Content $siteContent */
            $siteContent = $searchHit->valueObject;
            $searchHit->valueObject = $siteContent->contentInfo->innerContentInfo;
        }

        return $searchResult;
    }

    public function findLocations(LocationQuery $query, array $languageFilter = [], bool $filterOnUserPermissions = true): SearchResult
    {
        $searchResult = $this->filterService->filterLocations($query);

        foreach ($searchResult->searchHits as $searchHit) {
            /** @var \Netgen\IbexaSiteApi\API\Values\Location $siteLocation */
            $siteLocation = $searchHit->valueObject;
            $searchHit->valueObject = $siteLocation->innerLocation;
        }

        return $searchResult;
    }

    public function findSingle(Criterion $filter, array $languageFilter = [], bool $filterOnUserPermissions = true): Content
    {
        $query = new Query();
        $query->filter = $filter;
        $query->limit = 1;

        $searchResult = $this->filterService->filterContent($query);

        if ($searchResult->totalCount === 0) {
            throw new NotFoundException('Content', 'findSingle() found no content for given $filter');
        }

        if ($searchResult->totalCount > 1) {
            throw new InvalidArgumentException('totalCount', 'findSingle() found more then one item for given $filter');
        }

        /** @var \Netgen\IbexaSiteApi\API\Values\Content $siteContent */
        $siteContent = $searchResult->searchHits[0]->valueObject;

        return $siteContent->innerContent;
    }

    public function suggest(string $prefix, array $fieldPaths = [], int $limit = 10, ?Criterion $filter = null): void
    {
    }

    public function supports(int $capabilityFlag): bool
    {
        if ($this->searchHandler instanceof Capable) {
            return $this->searchHandler->supports($capabilityFlag);
        }

        return false;
    }
}
