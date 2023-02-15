<?php

declare(strict_types=1);

namespace Netgen\IbexaSiteApi\Core\Site;

use Ibexa\Contracts\Core\Repository\ContentService;
use Ibexa\Contracts\Core\Repository\LocationService;
use Ibexa\Contracts\Core\Repository\Repository;
use Ibexa\Contracts\Core\Repository\SearchService;
use Netgen\IbexaSiteApi\API\FilterService as APIFilterService;
use Netgen\IbexaSiteApi\API\FindService as APIFindService;
use Netgen\IbexaSiteApi\API\LanguageResolver as APILanguageResolver;
use Netgen\IbexaSiteApi\API\LoadService as APILoadService;
use Netgen\IbexaSiteApi\API\RelationService as APIRelationService;
use Netgen\IbexaSiteApi\API\Routing\UrlGenerator;
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
    private readonly ContentService $contentService;
    private readonly LocationService $locationService;
    private readonly SearchService $searchService;

    private ?DomainObjectMapper $domainObjectMapper = null;
    private ?APIFilterService $filterService = null;
    private ?APIFindService $findService = null;
    private ?APILoadService $loadService = null;
    private ?APIRelationService $relationService = null;

    public function __construct(
        private readonly BaseSettings $settings,
        private readonly APILanguageResolver $languageResolver,
        private readonly Repository $repository,
        private readonly SearchService $filteringSearchService,
        private readonly RelationResolverRegistry $relationResolverRegistry,
        private readonly UrlGenerator $urlGenerator,
        private readonly LoggerInterface $logger = new NullLogger(),
    ) {
        $this->contentService = $repository->getContentService();
        $this->locationService = $repository->getLocationService();
        $this->searchService = $repository->getSearchService();
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
                $this->languageResolver,
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
                $this->logger,
            );
        }

        return $this->relationService;
    }

    /**
     * @internal for Site API internal use only
     */
    public function getDomainObjectMapper(): DomainObjectMapper
    {
        if ($this->domainObjectMapper === null) {
            $this->domainObjectMapper = new DomainObjectMapper(
                $this,
                $this->repository,
                $this->urlGenerator,
                $this->settings->failOnMissingField,
                $this->logger,
            );
        }

        return $this->domainObjectMapper;
    }
}
