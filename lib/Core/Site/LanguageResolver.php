<?php

declare(strict_types=1);

namespace Netgen\IbexaSiteApi\Core\Site;

use Ibexa\Contracts\Core\Repository\Values\Content\Location;
use Ibexa\Contracts\Core\Repository\Values\Content\VersionInfo;
use Netgen\IbexaSiteApi\API\LanguageResolver as BaseLanguageResolver;
use Netgen\IbexaSiteApi\API\Settings as BaseSettings;
use Netgen\IbexaSiteApi\Core\Site\Exceptions\TranslationNotMatchedException;

final class LanguageResolver extends BaseLanguageResolver
{
    private BaseSettings $settings;

    public function __construct(BaseSettings $settings)
    {
        $this->settings = $settings;
    }

    public function resolveFromLanguage(VersionInfo $versionInfo, string $languageCode): string
    {
        if (!in_array($languageCode, $versionInfo->languageCodes, true)) {
            throw new TranslationNotMatchedException(
                $versionInfo->contentInfo->id,
                $this->getContext($versionInfo, $languageCode)
            );
        }

        return $languageCode;
    }

    public function resolveFromContent(VersionInfo $versionInfo): string
    {
        foreach ($this->settings->prioritizedLanguages as $languageCode) {
            if (in_array($languageCode, $versionInfo->languageCodes, true)) {
                return $languageCode;
            }
        }

        if ($this->settings->useAlwaysAvailable && $versionInfo->contentInfo->alwaysAvailable) {
            return $versionInfo->contentInfo->mainLanguageCode;
        }

        throw new TranslationNotMatchedException(
            $versionInfo->contentInfo->id,
            $this->getContext($versionInfo)
        );
    }

    public function resolveFromLocation(Location $location, VersionInfo $versionInfo): string
    {
        return $this->resolveFromContent($versionInfo);
    }

    /**
     * Returns an array describing language resolving context.
     *
     * To be used when throwing TranslationNotMatchedException.
     */
    private function getContext(VersionInfo $versionInfo, string $languageCode = null): array
    {
        $context = [
            'prioritizedLanguages' => $this->settings->prioritizedLanguages,
            'useAlwaysAvailable' => $this->settings->useAlwaysAvailable,
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
