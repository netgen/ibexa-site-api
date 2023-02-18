<?php

declare(strict_types=1);

namespace Netgen\Bundle\IbexaSiteApiBundle\Templating\Twig\Extension;

use Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException;
use Netgen\Bundle\IbexaSiteApiBundle\NamedObject\Provider;
use Netgen\IbexaSiteApi\API\Values\Content;
use Netgen\IbexaSiteApi\API\Values\Location;
use Netgen\TagsBundle\API\Repository\Values\Tags\Tag;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * NamedObjectExtension runtime.
 *
 * @see \Netgen\Bundle\IbexaSiteApiBundle\Templating\Twig\Extension\NamedObjectExtension
 */
class NamedObjectRuntime
{
    private Provider $namedObjectProvider;
    private bool $isDebug;
    private LoggerInterface $logger;

    public function __construct(
        Provider $specialObjectProvider,
        bool $isDebug,
        ?LoggerInterface $logger = null
    ) {
        $this->namedObjectProvider = $specialObjectProvider;
        $this->isDebug = $isDebug;
        $this->logger = $logger ?? new NullLogger();
    }

    public function getNamedContent(string $name): ?Content
    {
        try {
            if ($this->namedObjectProvider->hasContent($name)) {
                return $this->namedObjectProvider->getContent($name);
            }
        } catch (UnauthorizedException $e) {
            if ($this->isDebug) {
                $this->logger->debug($e->getMessage());
            }
        }

        return null;
    }

    public function getNamedLocation(string $name): ?Location
    {
        try {
            if ($this->namedObjectProvider->hasLocation($name)) {
                return $this->namedObjectProvider->getLocation($name);
            }
        } catch (UnauthorizedException $e) {
            if ($this->isDebug) {
                $this->logger->debug($e->getMessage());
            }
        }

        return null;
    }

    public function getNamedTag(string $name): ?Tag
    {
        try {
            if ($this->namedObjectProvider->hasTag($name)) {
                return $this->namedObjectProvider->getTag($name);
            }
        } catch (UnauthorizedException $e) {
            if ($this->isDebug) {
                $this->logger->debug($e->getMessage());
            }
        }

        return null;
    }
}
