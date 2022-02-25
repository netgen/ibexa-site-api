<?php

declare(strict_types=1);

namespace Netgen\IbexaSiteApi\Core\Site;

use Ibexa\Contracts\Core\Repository\ContentService;
use Ibexa\Contracts\Core\Repository\LocationService;
use Ibexa\Contracts\Core\Repository\Values\Content\Location as APILocation;
use Ibexa\Contracts\Core\Repository\Values\Content\VersionInfo;
use Netgen\IbexaSiteApi\API\LoadService as LoadServiceInterface;
use Netgen\IbexaSiteApi\API\Settings as BaseSettings;
use Netgen\IbexaSiteApi\API\Values\Content;
use Netgen\IbexaSiteApi\API\Values\Location;
use Netgen\IbexaSiteApi\Core\Site\Exceptions\TranslationNotMatchedException;
use function in_array;

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
    private BaseSettings $settings;
    private DomainObjectMapper $domainObjectMapper;
    private ContentService $contentService;
    private LocationService $locationService;

    public function __construct(
        BaseSettings $settings,
        DomainObjectMapper $domainObjectMapper,
        ContentService $contentService,
        LocationService $locationService
    ) {
        $this->settings = $settings;
        $this->domainObjectMapper = $domainObjectMapper;
        $this->contentService = $contentService;
        $this->locationService = $locationService;
    }

    public function loadContent(int $contentId, ?int $versionNo = null, ?string $languageCode = null): Content
    {
        $versionInfo = $this->contentService->loadVersionInfoById($contentId, $versionNo);
        $languageCode = $this->resolveLanguageCode($versionInfo, $languageCode);

        return $this->domainObjectMapper->mapContent($versionInfo, $languageCode);
    }

    public function loadContentByRemoteId(string $remoteId): Content
    {
        $contentInfo = $this->contentService->loadContentInfoByRemoteId($remoteId);

        return $this->loadContent($contentInfo->id);
    }

    public function loadLocation(int $locationId): Location
    {
        $location = $this->locationService->loadLocation($locationId);

        return $this->getSiteLocation($location);
    }

    public function loadLocationByRemoteId(string $remoteId): Location
    {
        $location = $this->locationService->loadLocationByRemoteId($remoteId);

        return $this->getSiteLocation($location);
    }

    /**
     * Returns Site Location object for the given Repository $location.
     *
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException
     * @throws \Netgen\IbexaSiteApi\Core\Site\Exceptions\TranslationNotMatchedException
     */
    private function getSiteLocation(APILocation $location): Location
    {
        $versionInfo = $this->contentService->loadVersionInfoById($location->contentInfo->id);
        $languageCode = $this->resolveLanguageCode($versionInfo);

        return $this->domainObjectMapper->mapLocation($location, $versionInfo, $languageCode);
    }

    /**
     * Returns the most prioritized language code for the given parameters.
     *
     * @throws \Netgen\IbexaSiteApi\Core\Site\Exceptions\TranslationNotMatchedException
     */
    private function resolveLanguageCode(VersionInfo $versionInfo, ?string $languageCode = null): string
    {
        if ($languageCode === null) {
            return $this->resolveLanguageCodeFromConfiguration($versionInfo);
        }

        if (!in_array($languageCode, $versionInfo->languageCodes, true)) {
            throw new TranslationNotMatchedException($versionInfo->contentInfo->id, $this->getContext($versionInfo));
        }

        return $languageCode;
    }

    /**
     * @throws \Netgen\IbexaSiteApi\Core\Site\Exceptions\TranslationNotMatchedException
     */
    private function resolveLanguageCodeFromConfiguration(VersionInfo $versionInfo): string
    {
        foreach ($this->settings->prioritizedLanguages as $languageCode) {
            if (in_array($languageCode, $versionInfo->languageCodes, true)) {
                return $languageCode;
            }
        }

        if ($this->settings->useAlwaysAvailable && $versionInfo->contentInfo->alwaysAvailable) {
            return $versionInfo->contentInfo->mainLanguageCode;
        }

        throw new TranslationNotMatchedException($versionInfo->contentInfo->id, $this->getContext($versionInfo));
    }

    /**
     * Returns an array describing language resolving context.
     *
     * To be used when throwing TranslationNotMatchedException.
     */
    private function getContext(VersionInfo $versionInfo): array
    {
        return [
            'prioritizedLanguages' => $this->settings->prioritizedLanguages,
            'useAlwaysAvailable' => $this->settings->useAlwaysAvailable,
            'availableTranslations' => $versionInfo->languageCodes,
            'mainTranslation' => $versionInfo->contentInfo->mainLanguageCode,
            'alwaysAvailable' => $versionInfo->contentInfo->alwaysAvailable,
        ];
    }
}
