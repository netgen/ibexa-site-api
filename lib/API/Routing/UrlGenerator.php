<?php

declare(strict_types=1);

namespace Netgen\IbexaSiteApi\API\Routing;

/**
 * UrlGenerator generates a URL for Site API Content and Location objects.
 *
 * The reference types defined here are the same as on Symfony's UrlGeneratorInterface
 * and declared in RFC 3986.
 *
 * @see \Netgen\IbexaSiteApi\API\Values\Content
 * @see \Netgen\IbexaSiteApi\API\Values\Location
 * @see \Symfony\Component\Routing\Generator\UrlGeneratorInterface
 * @see http://tools.ietf.org/html/rfc3986
 */
abstract class UrlGenerator
{
    /**
     * Generates an absolute URL, e.g. "https://netgen.io/netgen-stack-for-ibexa-ez-platform".
     */
    public const ABSOLUTE_URL = 0;

    /**
     * Generates an absolute path, e.g. "/netgen-stack-for-ibexa-ez-platform".
     */
    public const ABSOLUTE_PATH = 1;

    /**
     * Generates a relative path based on the current request path, e.g. "../netgen-stack-for-ibexa-ez-platform".
     */
    public const RELATIVE_PATH = 2;

    /**
     * Generates a network path, e.g. "//netgen.io/netgen-stack-for-ibexa-ez-platform".
     * Such reference reuses the current scheme but specifies the host.
     */
    public const NETWORK_PATH = 3;

    /**
     * @param object $object Site API Content or Location instance
     */
    abstract public function generate(
        object $object,
        array $parameters = [],
        int $referenceType = self::ABSOLUTE_PATH,
    ): string;
}
