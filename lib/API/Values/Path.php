<?php

declare(strict_types=1);

namespace Netgen\IbexaSiteApi\API\Values;

use Netgen\IbexaSiteApi\API\Routing\UrlGenerator;
use Netgen\IbexaSiteApi\Core\Site\Values\Content;
use Netgen\IbexaSiteApi\Core\Site\Values\Location;

/**
 * Site Url represents Location's URL path.
 *
 * URL path is generated for a Location, but both Content and Location can be passed to the generator.
 * When Content is passed, the URL path will be generated for Content's main Location.
 *
 * @see \Netgen\IbexaSiteApi\API\Routing\UrlGenerator
 *
 * When this object is cast to string, path will be generated using UrlGenerator::ABSOLUTE_PATH reference type.
 */
final class Path
{
    private UrlGenerator $urlGenerator;
    private $object;

    /**
     * @param Content|Location $object
     */
    public function __construct(UrlGenerator $urlGenerator, $object)
    {
        $this->urlGenerator = $urlGenerator;
        $this->object = $object;
    }

    public function __toString(): string
    {
        return $this->getAbsolute();
    }

    /**
     * @uses UrlGenerator::ABSOLUTE_PATH
     */
    public function getAbsolute(array $parameters = []): string
    {
        return $this->urlGenerator->generate($this->object, $parameters);
    }

    /**
     * @uses UrlGenerator::NETWORK_PATH
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
     * @uses UrlGenerator::RELATIVE_PATH
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
