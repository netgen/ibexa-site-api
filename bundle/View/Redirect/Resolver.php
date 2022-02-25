<?php

declare(strict_types=1);

namespace Netgen\Bundle\IbexaSiteApiBundle\View\Redirect;

use Netgen\Bundle\IbexaSiteApiBundle\Exception\InvalidRedirectConfiguration;
use Netgen\Bundle\IbexaSiteApiBundle\View\ContentView;
use Netgen\IbexaSiteApi\API\Values\Content;
use Netgen\IbexaSiteApi\API\Values\Location;
use Netgen\TagsBundle\API\Repository\Values\Tags\Tag;
use Symfony\Cmf\Component\Routing\RouteObjectInterface;
use Symfony\Component\Routing\Exception\InvalidParameterException;
use Symfony\Component\Routing\Exception\MissingMandatoryParametersException;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use function is_string;
use function mb_stripos;

final class Resolver
{
    private ParameterProcessor $parameterProcessor;
    private RouterInterface $router;

    public function __construct(
        ParameterProcessor $parameterProcessor,
        RouterInterface $router
    ) {
        $this->parameterProcessor = $parameterProcessor;
        $this->router = $router;
    }

    /**
     * Builds a path to the redirect target.
     *
     * @throws \Netgen\Bundle\IbexaSiteApiBundle\Exception\InvalidRedirectConfiguration
     */
    public function resolveTarget(RedirectConfiguration $redirectConfig, ContentView $view): string
    {
        if (mb_stripos($redirectConfig->getTarget(), '@=') === 0) {
            return $this->processExpression($redirectConfig, $view);
        }

        if (mb_stripos($redirectConfig->getTarget(), 'http') === 0) {
            return $redirectConfig->getTarget();
        }

        try {
            return $this->router->generate(
                $redirectConfig->getTarget(),
                $redirectConfig->getTargetParameters(),
                $redirectConfig->isAbsolute() ? UrlGeneratorInterface::ABSOLUTE_URL : UrlGeneratorInterface::ABSOLUTE_PATH
            );
        } catch (RouteNotFoundException | MissingMandatoryParametersException | InvalidParameterException $exception) {
            throw new InvalidRedirectConfiguration($redirectConfig->getTarget());
        }
    }

    /**
     * @throws \Netgen\Bundle\IbexaSiteApiBundle\Exception\InvalidRedirectConfiguration
     */
    private function processExpression(RedirectConfiguration $redirectConfig, ContentView $view): string
    {
        $value = $this->parameterProcessor->process(
            $redirectConfig->getTarget(),
            $view
        );

        if ($value instanceof Location || $value instanceof Content || $value instanceof Tag) {
            return $this->router->generate(
                '',
                [RouteObjectInterface::ROUTE_OBJECT => $value] + $redirectConfig->getTargetParameters(),
                $redirectConfig->isAbsolute() ? UrlGeneratorInterface::ABSOLUTE_URL : UrlGeneratorInterface::ABSOLUTE_PATH
            );
        }

        if (is_string($value) && mb_stripos($value, 'http') === 0) {
            return $value;
        }

        throw new InvalidRedirectConfiguration($redirectConfig->getTarget());
    }
}
