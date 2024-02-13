<?php

declare(strict_types=1);

namespace Netgen\Bundle\IbexaSiteApiBundle\Core\Site\Routing;

use Netgen\IbexaSiteApi\API\Routing\UrlGenerator as APIUrlGenerator;
use Netgen\IbexaSiteApi\API\Values\Content;
use Netgen\IbexaSiteApi\API\Values\Location;
use RuntimeException;
use Symfony\Cmf\Component\Routing\RouteObjectInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;

use function sprintf;

/**
 * UrlAliasUrlGenerator generates an Ibexa URL alias for the given object.
 */
class UrlGenerator extends APIUrlGenerator
{
    private RouterInterface $router;

    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }

    public function generate(
        object $object,
        array $parameters = [],
        int $referenceType = APIUrlGenerator::ABSOLUTE_PATH,
    ): string {
        if (!$object instanceof Content && !$object instanceof Location) {
            throw new RuntimeException(
                sprintf(
                    'Unsupported object, expected Site API Content or Location, got "%s"',
                    $object::class,
                ),
            );
        }

        return $this->router->generate(
            RouteObjectInterface::OBJECT_BASED_ROUTE_NAME,
            [RouteObjectInterface::ROUTE_OBJECT => $object] + $parameters,
            $this->mapReferenceType($referenceType),
        );
    }

    private function mapReferenceType(int $referenceType): int
    {
        switch ($referenceType) {
            case APIUrlGenerator::ABSOLUTE_URL:
                return UrlGeneratorInterface::ABSOLUTE_URL;

            case APIUrlGenerator::ABSOLUTE_PATH:
                return UrlGeneratorInterface::ABSOLUTE_PATH;

            case APIUrlGenerator::NETWORK_PATH:
                return UrlGeneratorInterface::NETWORK_PATH;

            case APIUrlGenerator::RELATIVE_PATH:
                return UrlGeneratorInterface::RELATIVE_PATH;
        }

        throw new RuntimeException(
            sprintf(
                'Unsupported reference type "%d"',
                $referenceType,
            ),
        );
    }
}
