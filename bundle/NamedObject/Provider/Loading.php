<?php

declare(strict_types=1);

namespace Netgen\Bundle\IbexaSiteApiBundle\NamedObject\Provider;

use Ibexa\Contracts\Core\SiteAccess\ConfigResolverInterface;
use InvalidArgumentException;
use Netgen\Bundle\IbexaSiteApiBundle\NamedObject\ParameterProcessor;
use Netgen\Bundle\IbexaSiteApiBundle\NamedObject\Provider;
use Netgen\IbexaSiteApi\API\LoadService;
use Netgen\IbexaSiteApi\API\Values\Content;
use Netgen\IbexaSiteApi\API\Values\Location;
use Netgen\TagsBundle\API\Repository\TagsService;
use Netgen\TagsBundle\API\Repository\Values\Tags\Tag;
use OutOfBoundsException;
use RuntimeException;
use function is_int;
use function is_string;

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
    private ParameterProcessor $parameterProcessor;
    private ConfigResolverInterface $configResolver;
    private ?array $configuration = null;

    public function __construct(
        LoadService $loadService,
        ?TagsService $tagsService,
        ParameterProcessor $parameterProcessor,
        ConfigResolverInterface $configResolver
    ) {
        $this->loadService = $loadService;
        $this->tagsService = $tagsService;
        $this->parameterProcessor = $parameterProcessor;
        $this->configResolver = $configResolver;
    }

    public function hasContent(string $name): bool
    {
        $this->setConfiguration();

        return isset($this->configuration['content'][$name]);
    }

    public function getContent(string $name): Content
    {
        if (!$this->hasContent($name)) {
            throw new OutOfBoundsException(
                'Named Content "' . $name . '" is not configured',
            );
        }

        $contentId = $this->getContentId($name);

        if (is_int($contentId)) {
            return $this->loadService->loadContent($contentId);
        }

        if (is_string($contentId)) {
            return $this->loadService->loadContentByRemoteId($contentId);
        }

        throw new InvalidArgumentException(
            'Named Content "' . $name . '" ID is not string or integer',
        );
    }

    public function hasLocation(string $name): bool
    {
        $this->setConfiguration();

        return isset($this->configuration['locations'][$name]);
    }

    public function getLocation(string $name): Location
    {
        if (!$this->hasLocation($name)) {
            throw new OutOfBoundsException(
                'Named Location "' . $name . '" is not configured',
            );
        }

        $locationId = $this->getLocationId($name);

        if (is_int($locationId)) {
            return $this->loadService->loadLocation($locationId);
        }

        if (is_string($locationId)) {
            return $this->loadService->loadLocationByRemoteId($locationId);
        }

        throw new InvalidArgumentException(
            'Named Location "' . $name . '" ID is not string or integer',
        );
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

        if (!$this->hasTag($name)) {
            throw new OutOfBoundsException(
                'Named Tag "' . $name . '" is not configured',
            );
        }

        $tagId = $this->getTagId($name);

        if (is_int($tagId)) {
            return $this->tagsService->loadTag($tagId);
        }

        if (is_string($tagId)) {
            return $this->tagsService->loadTagByRemoteId($tagId);
        }

        throw new InvalidArgumentException(
            'Named Tag "' . $name . '" ID is not string or integer',
        );
    }

    /**
     * @return string|int
     */
    private function getContentId(string $name)
    {
        $this->setConfiguration();

        return $this->parameterProcessor->process($this->configuration['content'][$name] ?? null);
    }

    /**
     * @return string|int
     */
    private function getLocationId(string $name)
    {
        $this->setConfiguration();

        return $this->parameterProcessor->process($this->configuration['locations'][$name] ?? null);
    }

    /**
     * @return string|int
     */
    private function getTagId(string $name)
    {
        $this->setConfiguration();

        return $this->parameterProcessor->process($this->configuration['tags'][$name] ?? null);
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
