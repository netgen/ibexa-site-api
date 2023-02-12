<?php

declare(strict_types=1);

namespace Netgen\Bundle\IbexaSiteApiBundle\Core\Site;

use Ibexa\Contracts\Core\Repository\Values\Content\Location;
use Ibexa\Contracts\Core\Repository\Values\Content\VersionInfo;
use Ibexa\Contracts\Core\SiteAccess\ConfigResolverInterface;
use Netgen\Bundle\IbexaSiteApiBundle\SiteAccess\Resolver;
use Netgen\IbexaSiteApi\API\LanguageResolver as BaseLanguageResolver;
use Netgen\IbexaSiteApi\API\Settings as BaseSettings;
use Netgen\IbexaSiteApi\Core\Site\Exceptions\TranslationNotMatchedException;

final class LanguageResolver extends BaseLanguageResolver
{
    private BaseSettings $settings;
    private Resolver $siteaccessResolver;
    private ConfigResolverInterface $configResolver;

    public function __construct(
        BaseSettings $settings,
        Resolver $siteaccessResolver,
        ConfigResolverInterface $configResolver
    ) {
        $this->settings = $settings;
        $this->siteaccessResolver = $siteaccessResolver;
        $this->configResolver = $configResolver;
    }

    public function resolveByLanguage(VersionInfo $versionInfo, string $languageCode): string
    {
        if (in_array($languageCode, $versionInfo->languageCodes, true)) {
            return $languageCode;
        }

        throw new TranslationNotMatchedException(
            $versionInfo->contentInfo->id,
            $this->getBaseContext($versionInfo, $languageCode)
        );
    }

    public function resolveByContent(VersionInfo $versionInfo): string
    {
        $siteaccess = $this->siteaccessResolver->resolveByContent($versionInfo->contentInfo);
        $prioritizedLanguages = $this->getPrioritizedLanguages($siteaccess);

        foreach ($prioritizedLanguages as $languageCode) {
            if (in_array($languageCode, $versionInfo->languageCodes, true)) {
                return $languageCode;
            }
        }

        if ($versionInfo->contentInfo->alwaysAvailable && $this->getIsAlwaysAvailable($siteaccess)) {
            return $versionInfo->contentInfo->mainLanguageCode;
        }

        $context = [
            'resolvedSiteaccess' => $siteaccess,
            'resolvedPrioritizedLanguages' => $prioritizedLanguages,
            'resolvedUseAlwaysAvailable' => $this->getIsAlwaysAvailable($siteaccess),
        ];
        $context += $this->getBaseContext($versionInfo);

        throw new TranslationNotMatchedException(
            $versionInfo->contentInfo->id,
            $context
        );
    }

    /**
     * @throws \Netgen\IbexaSiteApi\Core\Site\Exceptions\TranslationNotMatchedException
     */
    public function resolveByLocation(Location $location, VersionInfo $versionInfo): string
    {
        $siteaccess = $this->siteaccessResolver->resolveByLocation($location);
        $prioritizedLanguages = $this->getPrioritizedLanguages($siteaccess);

        foreach ($prioritizedLanguages as $languageCode) {
            if (in_array($languageCode, $versionInfo->languageCodes, true)) {
                return $languageCode;
            }
        }

        if ($versionInfo->contentInfo->alwaysAvailable && $this->getIsAlwaysAvailable($siteaccess)) {
            return $versionInfo->contentInfo->mainLanguageCode;
        }

        $context = [
            'locationId' => $location->id ?? null,
            'resolvedSiteaccess' => $siteaccess,
            'resolvedPrioritizedLanguages' => $prioritizedLanguages,
            'resolvedUseAlwaysAvailable' => $this->getIsAlwaysAvailable($siteaccess),
        ];
        $context += $this->getBaseContext($versionInfo);

        throw new TranslationNotMatchedException(
            $versionInfo->contentInfo->id,
            $context
        );
    }

    private function getPrioritizedLanguages(string $siteaccess): array
    {
        return $this->configResolver->getParameter('languages', null, $siteaccess);
    }

    private function getIsAlwaysAvailable(string $siteaccess): bool
    {
        return $this->configResolver->getParameter(
            'ng_site_api.use_always_available_fallback',
            null,
            $siteaccess
        );
    }

    /**
     * Returns an array describing base language resolving context.
     */
    private function getBaseContext(VersionInfo $versionInfo, string $languageCode = null): array
    {
        $context = [
            'currentPrioritizedLanguages' => $this->settings->prioritizedLanguages,
            'currentUseAlwaysAvailable' => $this->settings->useAlwaysAvailable,
            'availableTranslations' => $versionInfo->languageCodes,
            'contentId' => $versionInfo->contentInfo->id,
            'mainTranslation' => $versionInfo->contentInfo->mainLanguageCode,
            'alwaysAvailable' => $versionInfo->contentInfo->alwaysAvailable,
            'versionNumber' => $versionInfo->versionNo,
            'isPublished' => $versionInfo->isPublished(),
        ];

        if ($languageCode !== null) {
            $context['givenLanguageCode'] = $languageCode;
        }

        return $context;
    }
}
