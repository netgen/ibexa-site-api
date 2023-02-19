<?php

declare(strict_types=1);

namespace Netgen\IbexaSiteApi\Core\Site;

use Ibexa\Contracts\Core\Repository\ContentService;
use Ibexa\Contracts\Core\Repository\SearchService;
use Ibexa\Contracts\Core\Repository\Values\Content\LocationQuery;
use Ibexa\Contracts\Core\Repository\Values\Content\Query;
use Ibexa\Contracts\Core\Repository\Values\Content\Search\SearchResult;
use Netgen\IbexaSiteApi\API\FindService as FindServiceInterface;
use Netgen\IbexaSiteApi\API\Settings as BaseSettings;

/**
 * @final
 *
 * @internal
 *
 * Hint against API interface instead of this service:
 *
 * @see \Netgen\IbexaSiteApi\API\FindService
 */
class FindService implements FindServiceInterface
{
    public function __construct(
        private readonly BaseSettings $settings,
        private readonly DomainObjectMapper $domainObjectMapper,
        private readonly SearchService $searchService,
        private readonly ContentService $contentService
    ) {
    }

    public function findContent(Query $query): SearchResult
    {
        $searchResult = $this->searchService->findContentInfo(
            $query,
            [
                'languages' => $this->settings->prioritizedLanguages,
                'useAlwaysAvailable' => $this->settings->useAlwaysAvailable,
            ],
        );

        foreach ($searchResult->searchHits as $searchHit) {
            /** @var \Ibexa\Contracts\Core\Repository\Values\Content\ContentInfo $contentInfo */
            $contentInfo = $searchHit->valueObject;
            $searchHit->valueObject = $this->domainObjectMapper->mapContent(
                $this->contentService->loadVersionInfo(
                    $contentInfo,
                    $contentInfo->currentVersionNo,
                ),
                $searchHit->matchedTranslation,
            );
        }

        return $searchResult;
    }

    public function findLocations(LocationQuery $query): SearchResult
    {
        $searchResult = $this->searchService->findLocations(
            $query,
            [
                'languages' => $this->settings->prioritizedLanguages,
                'useAlwaysAvailable' => $this->settings->useAlwaysAvailable,
            ],
        );

        foreach ($searchResult->searchHits as $searchHit) {
            /** @var \Ibexa\Contracts\Core\Repository\Values\Content\Location $location */
            $location = $searchHit->valueObject;
            $searchHit->valueObject = $this->domainObjectMapper->mapLocation(
                $location,
                $this->contentService->loadVersionInfo(
                    $location->contentInfo,
                    $location->contentInfo->currentVersionNo,
                ),
                $searchHit->matchedTranslation,
            );
        }

        return $searchResult;
    }
}
