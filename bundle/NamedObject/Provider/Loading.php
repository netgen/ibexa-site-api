<?php

declare(strict_types=1);

namespace Netgen\Bundle\IbexaSiteApiBundle\NamedObject\Provider;

use Ibexa\Contracts\Core\SiteAccess\ConfigResolverInterface;
use LogicException;
use Netgen\Bundle\IbexaSiteApiBundle\NamedObject\Provider;
use Netgen\IbexaSiteApi\API\LoadService;
use Netgen\IbexaSiteApi\API\Values\Content;
use Netgen\IbexaSiteApi\API\Values\Location;
use Netgen\TagsBundle\API\Repository\TagsService;
use Netgen\TagsBundle\API\Repository\Values\Tags\Tag;
use RuntimeException;

/**
 * Loading named object provider provides named objects by loading them using
 * the appropriate repository services.
 *
 * @internal do not depend on this service, it can be changed without warning
 */
final class Loading extends Provider
{
    private LoadService $loadService;
    private ?TagsService $tagsService;
    private ConfigResolverInterface $configResolver;
    private ?array $configuration = null;

    public function __construct(
        LoadService $loadService,
        ?TagsService $tagsService,
        ConfigResolverInterface $configResolver
    ) {
        $this->loadService = $loadService;
        $this->tagsService = $tagsService;
        $this->configResolver = $configResolver;
    }

    public function hasContent(string $name): bool
    {
        $this->setConfiguration();

        return isset($this->configuration['content'][$name]);
    }

    public function getContent(string $name): Content
    {
        $contentId = $this->getContentId($name);

        if ($contentId !== null) {
            return $this->loadService->loadContent($contentId);
        }

        $contentRemoteId = $this->getContentRemoteId($name);

        if ($contentRemoteId !== null) {
            return $this->loadService->loadContentByRemoteId($contentRemoteId);
        }

        throw new LogicException('Named Content "' . $name . '" is not configured');
    }

    public function hasLocation(string $name): bool
    {
        $this->setConfiguration();

        return isset($this->configuration['locations'][$name]);
    }


    public function getLocation(string $name): Location
    {
        $locationId = $this->getLocationId($name);

        if ($locationId !== null) {
            return $this->loadService->loadLocation($locationId);
        }

        $locationRemoteId = $this->getLocationRemoteId($name);

        if ($locationRemoteId !== null) {
            return $this->loadService->loadLocationByRemoteId($locationRemoteId);
        }

        throw new LogicException('Named Location "' . $name . '" is not configured');
    }

    public function hasTag(string $name): bool
    {
        $this->setConfiguration();

        return isset($this->configuration['tags'][$name]);
    }

    public function getTag(string $name): Tag
    {
        if ($this->tagsService === null) {
            throw new RuntimeException('Missing Netgen TagsBundle package (netgen/tagsbundle)');
        }

        $tagId = $this->getTagId($name);

        if ($tagId !== null) {
            return $this->tagsService->loadTag($tagId);
        }

        $tagRemoteId = $this->getTagRemoteId($name);

        if ($tagRemoteId !== null) {
            return $this->tagsService->loadTagByRemoteId($tagRemoteId);
        }

        throw new LogicException('Named Tag "' . $name . '" is not configured');
    }

    private function getContentId(string $name): ?int
    {
        $this->setConfiguration();

        return $this->configuration['content'][$name]['id'] ?? null;
    }

    private function getContentRemoteId(string $name): ?string
    {
        $this->setConfiguration();

        return $this->configuration['content'][$name]['remote_id'] ?? null;
    }

    private function getLocationId(string $name): ?int
    {
        $this->setConfiguration();

        return $this->configuration['locations'][$name]['id'] ?? null;
    }

    private function getLocationRemoteId(string $name): ?string
    {
        $this->setConfiguration();

        return $this->configuration['locations'][$name]['remote_id'] ?? null;
    }

    private function getTagId(string $name): ?int
    {
        $this->setConfiguration();

        return $this->configuration['tags'][$name]['id'] ?? null;
    }

    private function getTagRemoteId(string $name): ?string
    {
        $this->setConfiguration();

        return $this->configuration['tags'][$name]['remote_id'] ?? null;
    }

    private function setConfiguration(): void
    {
        if ($this->configuration !== null) {
            return;
        }

        $configuration = $this->configResolver->getParameter('ng_site_api.named_objects');

        $this->configuration = $configuration ?? [];
    }
}
