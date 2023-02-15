<?php

declare(strict_types=1);

namespace Netgen\IbexaSiteApi\API\Values;

use Netgen\IbexaSiteApi\API\Routing\UrlGenerator;
use Netgen\IbexaSiteApi\Core\Site\Values\Content;
use Netgen\IbexaSiteApi\Core\Site\Values\Location;

/**
 * Site Url represents Location's URL.
 *
 * URL is generated for a Location, but both Content and Location can be passed to the generator.
 * When Content is passed, the URL will be generated for Content's main Location.
 *
 * @see \Netgen\IbexaSiteApi\API\Routing\UrlGenerator
 *
 * When cast to string, URL will be generated using UrlGenerator::ABSOLUTE_PATH reference type.
 */
class Url
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

    public function getAbsolutePath(string $siteaccess = null): string
    {
        return $this->urlGenerator->generate($this->object, $siteaccess);
    }

    public function getAbsoluteUrl(string $siteaccess = null): string
    {
        return $this->urlGenerator->generate(
            $this->object,
            $siteaccess,
            UrlGenerator::ABSOLUTE_URL
        );
    }

    public function getNetworkPath(string $siteaccess = null): string
    {
        return $this->urlGenerator->generate(
            $this->object,
            $siteaccess,
            UrlGenerator::NETWORK_PATH
        );
    }

    public function getRelativePath(string $siteaccess = null): string
    {
        return $this->urlGenerator->generate(
            $this->object,
            $siteaccess,
            UrlGenerator::RELATIVE_PATH
        );
    }

    public function __toString(): string
    {
        return $this->getAbsolutePath();
    }
}
