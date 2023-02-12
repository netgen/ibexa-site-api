<?php

declare(strict_types=1);

namespace Netgen\Bundle\IbexaSiteApiBundle\SiteAccess\Resolver;

use Ibexa\Contracts\Core\Persistence\Handler;
use Ibexa\Contracts\Core\Repository\Values\Content\ContentInfo;
use Ibexa\Contracts\Core\Repository\Values\Content\Location;
use Ibexa\Contracts\Core\SiteAccess\ConfigResolverInterface;
use Ibexa\Core\MVC\Symfony\SiteAccess;
use Netgen\Bundle\IbexaSiteApiBundle\SiteAccess\Resolver;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use function array_fill_keys;
use function array_keys;
use function array_map;
use function in_array;

/**
 * Default implementation of the CrossSiteaccessResolver.
 *
 * @final
 *
 * @internal do not depend on this service, it can be changed without warning
 */
class NativeResolver extends Resolver
{
    private Handler $persistenceHandler;
    private int $recursionLimit;
    private LoggerInterface $logger;

    private ConfigResolverInterface $configResolver;
    private SiteAccess $currentSiteaccess;
    private array $siteaccesses;
    private array $siteaccessGroupsBySiteaccess;

    private array $cache = [];

    public function __construct(Handler $persistenceHandler, int $recursionLimit, LoggerInterface $logger = null)
    {
        $this->persistenceHandler = $persistenceHandler;
        $this->recursionLimit = $recursionLimit;
        $this->logger = $logger ?? new NullLogger();
    }

    /**
     * @param ConfigResolverInterface $configResolver
     */
    public function setConfigResolver(ConfigResolverInterface $configResolver): void
    {
        $this->configResolver = $configResolver;
    }

    public function setSiteaccess(SiteAccess $currentSiteAccess = null): void
    {
        $this->currentSiteaccess = $currentSiteAccess;
    }

    public function setSiteaccessList(array $siteaccesses): void
    {
        $this->siteaccesses = $siteaccesses;
    }

    public function setSiteaccessGroupsBySiteaccess(array $siteaccessGroupsBySiteaccess): void
    {
        $this->siteaccessGroupsBySiteaccess = $siteaccessGroupsBySiteaccess;
    }

    /**
     * @throws \Exception
     */
    public function resolveByLocation(Location $location): string
    {
        $currentSiteaccess = $this->currentSiteaccess->name;

        if (!$this->isCrossSiteaccessContentEnabled()) {
            return $currentSiteaccess;
        }

        if (isset($this->cache['resolve'][$currentSiteaccess][$location->id])) {
            return $this->cache['resolve'][$currentSiteaccess][$location->id];
        }

        $siteaccess = $this->internalResolve($location);
        $this->cache['resolve'][$currentSiteaccess][$location->id] = $siteaccess;

        return $siteaccess;
    }

    public function resolveByContent(ContentInfo $contentInfo): string
    {
        return $this->currentSiteaccess->name;
    }

    private function isCrossSiteaccessContentEnabled(): bool
    {
        return $this->getParameter('enabled');
    }

    /**
     * @throws \Exception
     */
    private function internalResolve(Location $location): string
    {
        $siteaccessSet = $this->getSiteaccessSet($location);
        $currentSiteaccess = $this->currentSiteaccess->name;

        // Error: No siteaccesses were found for the Location, return the current siteaccess
        if (empty($siteaccessSet)) {
            $this->logger->error('Found no siteaccesses for Location #' . $location->id);

            return $currentSiteaccess;
        }

        // The Location is in the configured external subtree, use the current siteaccess
        if ($this->isInExternalSubtree($location)) {
            return $currentSiteaccess;
        }

        // Match try 1: If the current siteaccess was found, try to match it
        if (isset($siteaccessSet[$currentSiteaccess])) {
            $match = $this->matchBySiteaccess($location, $currentSiteaccess);

            if ($match !== null) {
                return $match;
            }
        }

        // Match try 2: Try to match to a siteaccess by prioritized language of the current siteaccess
        $currentPrioritizedLanguages = $this->getPrioritizedLanguages($currentSiteaccess);

        foreach ($currentPrioritizedLanguages as $language) {
            $match = $this->matchByPrioritizedLanguage($location, $language);

            if ($match !== null) {
                return $match;
            }
        }

        // Match try 3: Try to match any language, return the siteaccess with the highest positioned one
        // If configured, siteaccess for the main language match will be returned first if found
        $match = $this->matchByHighestPositionedLanguage($location);

        if ($match !== null) {
            return $match;
        }

        // Error: Nothing matched
        $this->logger->error('No siteaccess matched Location #' . $location->id);

        // Return the current SA if it was found
        if (isset($siteaccessSet[$currentSiteaccess])) {
            return $currentSiteaccess;
        }

        // Return the first SA from the found set
        return array_key_first($siteaccessSet);
    }

    /**
     * @throws \Exception
     */
    private function matchByHighestPositionedLanguage(Location $location): ?string
    {
        $siteaccessSet = $this->getSiteaccessSet($location);
        $locationLanguages = array_keys($this->getLanguageSet($location));
        $map = [];

        foreach (array_keys($siteaccessSet) as $siteaccess) {
            foreach ($locationLanguages as $language) {
                $position = $this->getLanguagePosition($siteaccess, $language);

                if ($position !== null) {
                    $map[$position][$language][] = $siteaccess;
                }
            }
        }

        if (empty($map)) {
            return null;
        }

        // Top matches first
        ksort($map, SORT_NUMERIC);

        // Try to return the main language at the topmost position if so configured
        if ($this->getParameter('prefer_main_language')) {
            foreach ($map as $languageMap) {
                if (isset($languageMap[$location->contentInfo->mainLanguageCode])) {
                    return reset($languageMap[$location->contentInfo->mainLanguageCode]);
                }
            }
        }

        // If the main language didn't match, first from the top will do
        $siteaccessesByLanguage = reset($map);
        $siteaccesses = reset($siteaccessesByLanguage);

        return reset($siteaccesses);
    }

    private function getLanguagePosition(string $siteaccess, string $language): ?int
    {
        if (isset($this->cache['language_position'][$siteaccess][$language])) {
            return $this->cache['language_position'][$siteaccess][$language] ?: null;
        }

        foreach ($this->getPrioritizedLanguages($siteaccess) as $position => $prioritizedLanguage) {
            if ($prioritizedLanguage === $language) {
                $this->cache['language_position'][$siteaccess][$language] = $position + 1;

                return $position + 1;
            }
        }

        $this->cache['language_position'][$siteaccess][$language] = false;

        return null;
    }

    private function isInExternalSubtree(Location $location): bool
    {
        $roots = $this->getParameter('external_subtree_roots');
        $rootSet = array_fill_keys($roots, true);

        foreach ($location->path as $id) {
            if (isset($rootSet[(int) $id])) {
                return true;
            }
        }

        return false;
    }

    /**
     * @throws \Exception
     */
    private function matchByPrioritizedLanguage(Location $location, string $language, int $position = 0): ?string
    {
        $recurse = false;
        $siteaccessSet = $this->getSiteaccessSet($location);

        foreach (array_keys($siteaccessSet) as $siteaccess) {
            $prioritizedLanguages = $this->getPrioritizedLanguages($siteaccess);
            $positionedLanguage = $prioritizedLanguages[$position] ?? null;
            $recurse = $recurse || isset($prioritizedLanguages[$position + 1]);

            if ($language !== $positionedLanguage) {
                continue;
            }

            $match = $this->matchBySiteaccess($location, $siteaccess);

            if ($match !== null) {
                return $match;
            }
        }

        $nextPosition = $position + 1;

        if (!$recurse || $nextPosition >= $this->recursionLimit) {
            return null;
        }

        return $this->matchByPrioritizedLanguage($location, $language, $nextPosition);
    }

    /**
     * @throws \Exception
     */
    private function matchBySiteaccess(Location $location, string $siteaccess): ?string
    {
        return $this->canShow($siteaccess, $location) ? $siteaccess : null;
    }

    /**
     * @throws \Exception
     */
    private function canShow(string $siteaccess, Location $location): bool
    {
        if (isset($this->cache['can_show'][$siteaccess][$location->id])) {
            return $this->cache['can_show'][$siteaccess][$location->id];
        }

        if ($location->contentInfo->alwaysAvailable) {
            $this->cache['can_show'][$siteaccess][$location->id] = true;

            return true;
        }

        $prioritizedLanguages = $this->getPrioritizedLanguages($siteaccess);
        $availableLanguageSet = $this->getLanguageSet($location);

        foreach ($prioritizedLanguages as $language) {
            if (isset($availableLanguageSet[$language])) {
                $this->cache['can_show'][$siteaccess][$location->id] = true;

                return true;
            }
        }

        $this->cache['can_show'][$siteaccess][$location->id] = false;

        return false;
    }

    /**
     * @throws \Exception
     *
     * @return string[]
     */
    private function getLanguageSet(Location $location): array
    {
        if (!isset($this->cache['location_available_language_set'][$location->id])) {
            $this->cache['location_available_language_set'][$location->id] = array_fill_keys(
                $this->persistenceHandler->contentHandler()->loadVersionInfo(
                    $location->contentId,
                    $location->contentInfo->currentVersionNo
                )->languageCodes,
                true
            );
        }

        return $this->cache['location_available_language_set'][$location->id];
    }

    /**
     * @return string[]
     */
    private function getPrioritizedLanguages(string $siteaccess): array
    {
        if (!isset($this->cache['prioritized_languages'][$siteaccess])) {
            $this->cache['prioritized_languages'][$siteaccess] = $this->configResolver->getParameter(
                'languages',
                null,
                $siteaccess
            );
        }

        return $this->cache['prioritized_languages'][$siteaccess];
    }

    private function getSiteaccessSet(Location $location): array
    {
        $currentSiteaccess = $this->currentSiteaccess->name;

        if (isset($this->cache['location_siteaccess_set'][$currentSiteaccess][$location->id])) {
            return $this->cache['location_siteaccess_set'][$currentSiteaccess][$location->id];
        }

        $ancestorAndSelfLocationIds = array_map('\intval', $location->path);
        $this->initializeSiteaccessRootLocationIdMap();
        $map = $this->cache['siteaccess_root_location_id_map'][$currentSiteaccess];
        $siteaccessSet = [];

        foreach ($map as $siteaccess => $rootLocationId) {
            if (in_array($rootLocationId, $ancestorAndSelfLocationIds, true)) {
                $siteaccessSet[$siteaccess] = true;
            }
        }

        $this->cache['location_siteaccess_set'][$currentSiteaccess][$location->id] = $siteaccessSet;

        return $siteaccessSet;
    }

    private function initializeSiteaccessRootLocationIdMap(): void
    {
        $currentSiteaccess = $this->currentSiteaccess->name;

        if (isset($this->cache['siteaccess_root_location_id_map'][$currentSiteaccess])) {
            return;
        }

        $this->cache['siteaccess_root_location_id_map'][$currentSiteaccess] = [];

        foreach ($this->siteaccesses as $siteaccess) {
            if ($this->isSiteaccessExcluded($siteaccess) || !$this->isSiteaccessIncluded($siteaccess)) {
                continue;
            }

            $rootLocationId = $this->configResolver->getParameter(
                'content.tree_root.location_id',
                null,
                $siteaccess
            );

            $this->cache['siteaccess_root_location_id_map'][$currentSiteaccess][$siteaccess] = $rootLocationId;
        }
    }

    private function isSiteaccessIncluded(string $siteaccess): bool
    {
        if ($siteaccess === $this->currentSiteaccess->name) {
            return true;
        }

        $includedSiteaccesses = $this->getParameter('included_siteaccesses');
        $includedSiteaccessGroups = $this->getParameter('included_siteaccess_groups');

        if (empty($includedSiteaccesses) && empty($includedSiteaccessGroups)) {
            return true;
        }

        if (!empty($includedSiteaccesses) && empty($includedSiteaccessGroups)) {
            return $this->isSiteaccessSiteaccessIncluded($siteaccess);
        }

        if (empty($includedSiteaccesses) && !empty($includedSiteaccessGroups)) {
            return $this->isSiteaccessSiteaccessGroupIncluded($siteaccess);
        }

        return $this->isSiteaccessSiteaccessIncluded($siteaccess)
            || $this->isSiteaccessSiteaccessGroupIncluded($siteaccess);
    }

    private function isSiteaccessSiteaccessIncluded(string $siteaccess): bool
    {
        $includedSiteaccesses = $this->getParameter('included_siteaccesses');
        $includedSiteaccessSet = array_fill_keys($includedSiteaccesses, true);

        return isset($includedSiteaccessSet[$siteaccess]);
    }

    private function isSiteaccessSiteaccessGroupIncluded(string $siteaccess): bool
    {
        $includedSiteaccessGroups = $this->getParameter('included_siteaccess_groups');
        $includedSiteaccessGroupSet = array_fill_keys($includedSiteaccessGroups, true);
        $siteaccessGroups = $this->siteaccessGroupsBySiteaccess[$siteaccess] ?? [];

        foreach ($siteaccessGroups as $siteaccessGroup) {
            if (isset($includedSiteaccessGroupSet[$siteaccessGroup])) {
                return true;
            }
        }

        return false;
    }

    private function isSiteaccessExcluded(string $siteaccess): bool
    {
        $excludedSiteaccessSet = array_fill_keys(
            $this->getParameter('excluded_siteaccesses'),
            true
        );

        if (isset($excludedSiteaccessSet[$siteaccess])) {
            return true;
        }

        $excludedSiteaccessGroupSet = array_fill_keys(
            $this->getParameter('excluded_siteaccess_groups'),
            true
        );

        $siteaccessGroups = $this->siteaccessGroupsBySiteaccess[$siteaccess] ?? [];

        foreach ($siteaccessGroups as $siteaccessGroup) {
            if (isset($excludedSiteaccessGroupSet[$siteaccessGroup])) {
                return true;
            }
        }

        return false;
    }

    private function getParameter(string $name)
    {
        $currentSiteaccess = $this->currentSiteaccess->name;

        if (isset($this->cache['parameters'][$currentSiteaccess][$name])) {
            return $this->cache['parameters'][$currentSiteaccess][$name];
        }

        $parameter = $this->configResolver->getParameter('ng_site_api.cross_siteaccess_content.' . $name);
        $this->cache['parameters'][$currentSiteaccess][$name] = $parameter;

        return $parameter;
    }
}
