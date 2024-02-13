<?php

declare(strict_types=1);

namespace Netgen\IbexaSiteApi\API\Values;

use Netgen\IbexaSiteApi\API\Routing\UrlGenerator;

/**
 * Site Url represents Content or Location URL path.
 *
 * @see \Netgen\IbexaSiteApi\API\Routing\UrlGenerator
 * @see \Netgen\IbexaSiteApi\API\Values\Content
 * @see \Netgen\IbexaSiteApi\API\Values\Location
 *
 * When this object is cast to string, path will be generated using UrlGenerator::ABSOLUTE_PATH reference type.
 */
final class Path
{
    private UrlGenerator $urlGenerator;
    private Content|Location $object;

    public function __construct(UrlGenerator $urlGenerator, Content|Location $object)
    {
        $this->urlGenerator = $urlGenerator;
        $this->object = $object;
    }

    public function __toString(): string
    {
        return $this->getAbsolute();
    }

    /**
     * @uses \Netgen\IbexaSiteApi\API\Routing\UrlGenerator::ABSOLUTE_PATH
     */
    public function getAbsolute(array $parameters = []): string
    {
        return $this->urlGenerator->generate($this->object, $parameters);
    }

    /**
     * @uses \Netgen\IbexaSiteApi\API\Routing\UrlGenerator::NETWORK_PATH
     */
    public function getNetwork(array $parameters = []): string
    {
        return $this->urlGenerator->generate(
            $this->object,
            $parameters,
            UrlGenerator::NETWORK_PATH,
        );
    }

    /**
     * @uses \Netgen\IbexaSiteApi\API\Routing\UrlGenerator::RELATIVE_PATH
     */
    public function getRelative(array $parameters = []): string
    {
        return $this->urlGenerator->generate(
            $this->object,
            $parameters,
            UrlGenerator::RELATIVE_PATH,
        );
    }
}
