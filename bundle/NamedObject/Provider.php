<?php

declare(strict_types=1);

namespace Netgen\Bundle\IbexaSiteApiBundle\NamedObject;

use Netgen\IbexaSiteApi\API\Values\Content;
use Netgen\IbexaSiteApi\API\Values\Location;
use Netgen\TagsBundle\API\Repository\Values\Tags\Tag;

/**
 * Named object provider abstract.
 */
abstract class Provider
{
    abstract public function hasContent(string $name): bool;

    /**
     * @throws \Netgen\IbexaSiteApi\API\Exceptions\TranslationNotMatchedException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException
     */
    abstract public function getContent(string $name): Content;

    abstract public function hasLocation(string $name): bool;

    /**
     * @throws \Netgen\IbexaSiteApi\API\Exceptions\TranslationNotMatchedException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException
     */
    abstract public function getLocation(string $name): Location;

    abstract public function hasTag(string $name): bool;

    /**
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException
     */
    abstract public function getTag(string $name): Tag;
}
