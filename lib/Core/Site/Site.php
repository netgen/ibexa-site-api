<?php

declare(strict_types=1);

namespace Netgen\IbexaSiteApi\Core\Site;

use Ibexa\Contracts\Core\Repository\ContentService;
use Ibexa\Contracts\Core\Repository\LocationService;
use Ibexa\Contracts\Core\Repository\Repository;
use Ibexa\Contracts\Core\Repository\SearchService;
use Netgen\IbexaSiteApi\API\FilterService as APIFilterService;
use Netgen\IbexaSiteApi\API\FindService as APIFindService;
use Netgen\IbexaSiteApi\API\LoadService as APILoadService;
use Netgen\IbexaSiteApi\API\RelationService as APIRelationService;
use Netgen\IbexaSiteApi\API\Settings as BaseSettings;
use Netgen\IbexaSiteApi\API\Site as SiteInterface;
use Netgen\IbexaSiteApi\Core\Site\Plugins\FieldType\RelationResolver\Registry as RelationResolverRegistry;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * @final
 *
 * @internal
 *
 * Hint against API interface instead of this service:
 *
 * @see \Netgen\IbexaSiteApi\API\Site
 */
class Site implements SiteInterface
{
    private BaseSettings $settings;
    private ContentService $contentService;
    private LocationService $locationService;
    private SearchService $searchService;
    private SearchService $filteringSearchService;
    private RelationResolverRegistry $relationResolverRegistry;
    private Repository $repository;
    private LoggerInterface $logger;

    private ?DomainObjectMapper $domainObjectMapper = null;
    private ?APIFilterService $filterService = null;
    private ?APIFindService $findService = null;
    private ?APILoadService $loadService = null;
    private ?APIRelationService $relationService = null;

    public function __construct(
        BaseSettings $settings,
        Repository $repository,
        SearchService $filteringSearchService,
        RelationResolverRegistry $relationResolverRegistry,
        ?LoggerInterface $logger = null
    ) {
        $this->settings = $settings;
        $this->repository = $repository;
        $this->contentService = $repository->getContentService();
        $this->locationService = $repository->getLocationService();
        $this->searchService = $repository->getSearchService();
        $this->filteringSearchService = $filteringSearchService;
        $this->relationResolverRegistry = $relationResolverRegistry;
        $this->logger = $logger ?? new NullLogger();
    }

    public function getSettings(): BaseSettings
    {
        return $this->settings;
    }

    public function getFilterService(): APIFilterService
    {
        if ($this->filterService === null) {
            $this->filterService = new FilterService(
                $this->settings,
                $this->getDomainObjectMapper(),
                $this->filteringSearchService,
                $this->contentService,
            );
        }

        return $this->filterService;
    }

    public function getFindService(): APIFindService
    {
        if ($this->findService === null) {
            $this->findService = new FindService(
                $this->settings,
                $this->getDomainObjectMapper(),
                $this->searchService,
                $this->contentService,
            );
        }

        return $this->findService;
    }

    public function getLoadService(): APILoadService
    {
        if ($this->loadService === null) {
            $this->loadService = new LoadService(
                $this->settings,
                $this->getDomainObjectMapper(),
                $this->contentService,
                $this->locationService,
            );
        }

        return $this->loadService;
    }

    public function getRelationService(): APIRelationService
    {
        if ($this->relationService === null) {
            $this->relationService = new RelationService(
                $this,
                $this->relationResolverRegistry,
            );
        }

        return $this->relationService;
    }

    /**
     * @internal for Site API internal use only
     *
     * @return \Netgen\IbexaSiteApi\Core\Site\DomainObjectMapper
     */
    public function getDomainObjectMapper(): DomainObjectMapper
    {
        if ($this->domainObjectMapper === null) {
            $this->domainObjectMapper = new DomainObjectMapper(
                $this,
                $this->repository,
                $this->settings->failOnMissingField,
                $this->logger,
            );
        }

        return $this->domainObjectMapper;
    }
}
