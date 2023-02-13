<?php

declare(strict_types=1);

namespace Netgen\Bundle\IbexaSiteApiBundle\View\Redirect;

use Netgen\Bundle\IbexaSiteApiBundle\Exception\InvalidRedirectConfiguration;
use Netgen\IbexaSiteApi\API\Values\Content;
use Netgen\IbexaSiteApi\API\Values\Location;
use Netgen\TagsBundle\API\Repository\Values\Tags\Tag;
use Symfony\Cmf\Component\Routing\RouteObjectInterface;
use Symfony\Component\Routing\Exception\InvalidParameterException;
use Symfony\Component\Routing\Exception\MissingMandatoryParametersException;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;

use function get_class;
use function gettype;
use function is_object;
use function is_string;
use function mb_stripos;

final class Resolver
{
    private RouterInterface $router;

    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }

    /**
     * Builds a path to the redirect target.
     *
     * @throws \Netgen\Bundle\IbexaSiteApiBundle\Exception\InvalidRedirectConfiguration
     */
    public function resolveTarget(RedirectConfiguration $redirectConfig): string
    {
        $target = $redirectConfig->getTarget();

        if (!is_string($target) && !is_object($target)) {
            throw new InvalidRedirectConfiguration(gettype($target));
        }

        if (is_object($target)) {
            return $this->resolveForObject($target, $redirectConfig);
        }

        if (mb_stripos($target, 'http') === 0) {
            return $target;
        }

        return $this->resolveForRoute($target, $redirectConfig);
    }

    /**
     * @throws \Netgen\Bundle\IbexaSiteApiBundle\Exception\InvalidRedirectConfiguration
     */
    private function resolveForObject(object $object, RedirectConfiguration $redirectConfig): string
    {
        if ($object instanceof Location || $object instanceof Content || $object instanceof Tag) {
            return $this->router->generate(
                '',
                [RouteObjectInterface::ROUTE_OBJECT => $object] + $redirectConfig->getTargetParameters(),
                $this->resolveReferenceType($redirectConfig),
            );
        }

        throw new InvalidRedirectConfiguration(get_class($object));
    }

    /**
     * @throws \Netgen\Bundle\IbexaSiteApiBundle\Exception\InvalidRedirectConfiguration
     */
    private function resolveForRoute(string $route, RedirectConfiguration $redirectConfig): string
    {
        try {
            return $this->router->generate(
                $route,
                $redirectConfig->getTargetParameters(),
                $this->resolveReferenceType($redirectConfig),
            );
        } catch (RouteNotFoundException|MissingMandatoryParametersException|InvalidParameterException $exception) {
            throw new InvalidRedirectConfiguration($route, $exception);
        }
    }

    private function resolveReferenceType(RedirectConfiguration $redirectConfig): int
    {
        if ($redirectConfig->isAbsolute()) {
            return UrlGeneratorInterface::ABSOLUTE_URL;
        }

        return UrlGeneratorInterface::ABSOLUTE_PATH;
    }
}
