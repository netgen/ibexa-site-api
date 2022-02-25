<?php

declare(strict_types=1);

namespace Netgen\IbexaSiteApi\Core\Site;

use Ibexa\Contracts\Core\Repository\ContentService;
use Ibexa\Contracts\Core\Repository\SearchService;
use Ibexa\Contracts\Core\Repository\Values\Content\LocationQuery;
use Ibexa\Contracts\Core\Repository\Values\Content\Query;
use Ibexa\Contracts\Core\Repository\Values\Content\Search\SearchResult;
use Netgen\IbexaSiteApi\API\FilterService as FilterServiceInterface;
use Netgen\IbexaSiteApi\API\Settings as BaseSettings;

/**
 * @final
 *
 * @internal
 *
 * Hint against API interface instead of this service:
 *
 * @see \Netgen\IbexaSiteApi\API\FilterService
 */
class FilterService implements FilterServiceInterface
{
    private BaseSettings $settings;
    private DomainObjectMapper $domainObjectMapper;
    private SearchService $searchService;
    private ContentService $contentService;

    public function __construct(
        BaseSettings $settings,
        DomainObjectMapper $domainObjectMapper,
        SearchService $searchService,
        ContentService $contentService
    ) {
        $this->settings = $settings;
        $this->domainObjectMapper = $domainObjectMapper;
        $this->searchService = $searchService;
        $this->contentService = $contentService;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException
     */
    public function filterContent(Query $query): SearchResult
    {
        $searchResult = $this->searchService->findContentInfo(
            $query,
            [
                'languages' => $this->settings->prioritizedLanguages,
                'useAlwaysAvailable' => $this->settings->useAlwaysAvailable,
            ]
        );

        foreach ($searchResult->searchHits as $searchHit) {
            /** @var \Ibexa\Contracts\Core\Repository\Values\Content\ContentInfo $contentInfo */
            $contentInfo = $searchHit->valueObject;
            $searchHit->valueObject = $this->domainObjectMapper->mapContent(
                $this->contentService->loadVersionInfo(
                    $contentInfo,
                    $contentInfo->currentVersionNo
                ),
                $searchHit->matchedTranslation
            );
        }

        return $searchResult;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException
     */
    public function filterLocations(LocationQuery $query): SearchResult
    {
        $searchResult = $this->searchService->findLocations(
            $query,
            [
                'languages' => $this->settings->prioritizedLanguages,
                'useAlwaysAvailable' => $this->settings->useAlwaysAvailable,
            ]
        );

        foreach ($searchResult->searchHits as $searchHit) {
            /** @var \Ibexa\Contracts\Core\Repository\Values\Content\Location $location */
            $location = $searchHit->valueObject;
            $searchHit->valueObject = $this->domainObjectMapper->mapLocation(
                $location,
                $this->contentService->loadVersionInfo(
                    $location->contentInfo,
                    $location->contentInfo->currentVersionNo
                ),
                $searchHit->matchedTranslation
            );
        }

        return $searchResult;
    }
}
