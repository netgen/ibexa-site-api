<?php

declare(strict_types=1);

namespace Netgen\IbexaSiteApi\Core\Site;

use Ibexa\Contracts\Core\Repository\ContentService;
use Ibexa\Contracts\Core\Repository\LocationService;
use Ibexa\Contracts\Core\Repository\Values\Content\Location as APILocation;
use Netgen\IbexaSiteApi\API\LanguageResolver as APILanguageResolver;
use Netgen\IbexaSiteApi\API\LoadService as LoadServiceInterface;
use Netgen\IbexaSiteApi\API\Values\Content;
use Netgen\IbexaSiteApi\API\Values\Location;

/**
 * @final
 *
 * @internal
 *
 * Hint against API interface instead of this service:
 *
 * @see \Netgen\IbexaSiteApi\API\LoadService
 */
class LoadService implements LoadServiceInterface
{
    public function __construct(
        private readonly APILanguageResolver $languageResolver,
        private readonly DomainObjectMapper $domainObjectMapper,
        private readonly ContentService $contentService,
        private readonly LocationService $locationService,
    ) {
    }

    public function loadContent(int $contentId): Content
    {
        $versionInfo = $this->contentService->loadVersionInfoById($contentId);

        $mainLocationId = $versionInfo->getContentInfo()->mainLocationId;
        $location = $mainLocationId ? $this->locationService->loadLocation($mainLocationId, []) : null;

        if ($location !== null) {
            $languageCode = $this->languageResolver->resolveByLocation($location, $versionInfo);
        } else {
            $languageCode = $this->languageResolver->resolveByContent($versionInfo);
        }

        return $this->domainObjectMapper->mapContent($versionInfo, $languageCode);
    }

    public function loadContentForPreview(int $contentId, int $versionNo, string $languageCode): Content
    {
        $versionInfo = $this->contentService->loadVersionInfoById($contentId, $versionNo);
        $languageCode = $this->languageResolver->resolveForPreview($versionInfo, $languageCode);

        return $this->domainObjectMapper->mapContent($versionInfo, $languageCode);
    }

    public function loadContentByRemoteId(string $remoteId): Content
    {
        $contentInfo = $this->contentService->loadContentInfoByRemoteId($remoteId);

        return $this->loadContent($contentInfo->id);
    }

    public function loadLocation(int $locationId): Location
    {
        $location = $this->locationService->loadLocation($locationId, []);

        return $this->getSiteLocation($location);
    }

    public function loadLocationByRemoteId(string $remoteId): Location
    {
        $location = $this->locationService->loadLocationByRemoteId($remoteId, []);

        return $this->getSiteLocation($location);
    }

    /**
     * Returns Site Location object for the given Repository $location.
     */
    private function getSiteLocation(APILocation $location): Location
    {
        $versionInfo = $this->contentService->loadVersionInfoById($location->contentInfo->id);
        $languageCode = $this->languageResolver->resolveByLocation($location, $versionInfo);

        return $this->domainObjectMapper->mapLocation($location, $versionInfo, $languageCode);
    }
}
