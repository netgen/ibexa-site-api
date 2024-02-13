<?php

declare(strict_types=1);

namespace Netgen\IbexaSiteApi\API\Values;

use Netgen\IbexaSiteApi\API\Routing\UrlGenerator;

/**
 * Site Url represents Content or Location URL.
 *
 * @see \Netgen\IbexaSiteApi\API\Routing\UrlGenerator
 * @see \Netgen\IbexaSiteApi\API\Values\Content
 * @see \Netgen\IbexaSiteApi\API\Values\Location
 */
final class Url
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
        return $this->get();
    }

    /**
     * @uses \Netgen\IbexaSiteApi\API\Routing\UrlGenerator::ABSOLUTE_URL
     */
    public function get(array $parameters = []): string
    {
        return $this->urlGenerator->generate(
            $this->object,
            $parameters,
            UrlGenerator::ABSOLUTE_URL,
        );
    }
}
