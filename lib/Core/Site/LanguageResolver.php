<?php

declare(strict_types=1);

namespace Netgen\IbexaSiteApi\Core\Site;

use Ibexa\Contracts\Core\Repository\Values\Content\Location;
use Ibexa\Contracts\Core\Repository\Values\Content\VersionInfo;
use Netgen\IbexaSiteApi\API\LanguageResolver as BaseLanguageResolver;
use Netgen\IbexaSiteApi\API\Settings as BaseSettings;
use Netgen\IbexaSiteApi\Core\Site\Exceptions\TranslationNotMatchedException;

use function in_array;

final class LanguageResolver extends BaseLanguageResolver
{
    public function __construct(
        private readonly BaseSettings $settings,
    ) {}

    public function resolveForPreview(VersionInfo $versionInfo, string $languageCode): string
    {
        if (in_array($languageCode, $versionInfo->languageCodes, true)) {
            return $languageCode;
        }

        throw new TranslationNotMatchedException(
            $versionInfo->contentInfo->id,
            $this->getContext($versionInfo, $languageCode),
        );
    }

    public function resolveByContent(VersionInfo $versionInfo): string
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
            $this->getContext($versionInfo),
        );
    }

    public function resolveByLocation(Location $location, VersionInfo $versionInfo): string
    {
        return $this->resolveByContent($versionInfo);
    }

    /**
     * Returns an array describing language resolving context.
     *
     * To be used when throwing TranslationNotMatchedException.
     */
    private function getContext(VersionInfo $versionInfo, ?string $languageCode = null): array
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
