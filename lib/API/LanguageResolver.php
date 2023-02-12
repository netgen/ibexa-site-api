<?php

declare(strict_types=1);

namespace Netgen\IbexaSiteApi\API;

use Ibexa\Contracts\Core\Repository\Values\Content\Location;
use Ibexa\Contracts\Core\Repository\Values\Content\VersionInfo;

abstract class LanguageResolver
{
    /**
     * @throws \Netgen\IbexaSiteApi\Core\Site\Exceptions\TranslationNotMatchedException
     */
    abstract public function resolveByLanguage(VersionInfo $versionInfo, string $languageCode): string;

    /**
     * @throws \Netgen\IbexaSiteApi\Core\Site\Exceptions\TranslationNotMatchedException
     */
    abstract public function resolveByContent(VersionInfo $versionInfo): string;

    /**
     * @throws \Netgen\IbexaSiteApi\Core\Site\Exceptions\TranslationNotMatchedException
     */
    abstract public function resolveByLocation(Location $location, VersionInfo $versionInfo): string;
}
