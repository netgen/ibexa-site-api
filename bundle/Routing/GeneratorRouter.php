<?php

declare(strict_types=1);

namespace Netgen\Bundle\IbexaSiteApiBundle\Routing;

use Ibexa\Contracts\Core\Repository\Repository;
use Ibexa\Contracts\Core\Repository\Values\Content\Content as APIContent;
use Ibexa\Contracts\Core\Repository\Values\Content\ContentInfo as APIContentInfo;
use Ibexa\Contracts\Core\Repository\Values\Content\Location as APILocation;
use Ibexa\Contracts\Core\SiteAccess\ConfigResolverInterface;
use Ibexa\Core\MVC\Symfony\Routing\Generator\UrlAliasGenerator;
use Ibexa\Core\MVC\Symfony\Routing\UrlAliasRouter as CoreUrlAliasRouter;
use LogicException;
use Netgen\Bundle\IbexaSiteApiBundle\SiteAccess\Resolver;
use Netgen\IbexaSiteApi\API\Values\Content;
use Netgen\IbexaSiteApi\API\Values\ContentInfo;
use Netgen\IbexaSiteApi\API\Values\Location;
use RuntimeException;
use Symfony\Cmf\Component\Routing\ChainedRouterInterface;
use Symfony\Cmf\Component\Routing\RouteObjectInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\Matcher\RequestMatcherInterface;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Route as SymfonyRoute;
use Symfony\Component\Routing\RouteCollection;

use function is_object;

/**
 * @final
 *
 * @internal do not use directly; use @router instead to keep the chain routing mechanism
 */
class GeneratorRouter implements ChainedRouterInterface, RequestMatcherInterface
{
    private Repository $repository;
    private UrlAliasGenerator $generator;
    private Resolver $siteaccessResolver;
    private RequestContext $requestContext;
    private ConfigResolverInterface $configResolver;

    public function __construct(
        Repository $repository,
        UrlAliasGenerator $generator,
        Resolver $siteaccessResolver,
        RequestContext $requestContext,
        ConfigResolverInterface $configResolver
    ) {
        $this->repository = $repository;
        $this->generator = $generator;
        $this->siteaccessResolver = $siteaccessResolver;
        $this->requestContext = $requestContext;
        $this->configResolver = $configResolver;
    }

    /**
     * @throws \Exception
     */
    public function generate(
        string $name,
        array $parameters = [],
        int $referenceType = UrlGeneratorInterface::ABSOLUTE_PATH
    ): string {
        $isSiteApiPrimaryContentView = $this->configResolver->getParameter('ng_site_api.site_api_is_primary_content_view');

        if (!$isSiteApiPrimaryContentView) {
            throw new RouteNotFoundException('Pass to the next router');
        }

        $location = $this->resolveLocation($name, $parameters);

        unset(
            $parameters['location'],
            $parameters['locationId'],
            $parameters['content'],
            $parameters['contentInfo'],
            $parameters['contentId'],
            $parameters['viewType'],
            $parameters['layout'],
            $parameters[RouteObjectInterface::ROUTE_OBJECT],
        );

        if (isset($parameters['siteaccess'])) {
            return $this->generator->generate($location, $parameters, $referenceType);
        }

        return $this->resolveSiteaccessAndGenerate($location, $parameters, $referenceType);
    }

    public function supports($name): bool
    {
        if (is_object($name)) {
            return $this->supportsObject($name);
        }

        return $name === CoreUrlAliasRouter::URL_ALIAS_ROUTE_NAME
            || $name === RouteObjectInterface::OBJECT_BASED_ROUTE_NAME
            || $name === '';
    }

    public function setContext(RequestContext $context): void
    {
        $this->requestContext = $context;
        $this->generator->setRequestContext($context);
    }

    public function getContext(): RequestContext
    {
        return $this->requestContext;
    }

    public function getRouteCollection(): RouteCollection
    {
        return new RouteCollection();
    }

    public function match(string $pathinfo): array
    {
        throw new ResourceNotFoundException('Pass to the next router');
    }

    public function matchRequest(Request $request): array
    {
        throw new ResourceNotFoundException('Pass to the next router');
    }

    public function getRouteDebugMessage($name, array $parameters = []): string
    {
        if ($name instanceof RouteObjectInterface) {
            return 'Route with key ' . $name->getRouteKey();
        }

        if ($name instanceof SymfonyRoute) {
            return 'Route with pattern ' . $name->getPath();
        }

        return $name;
    }

    /**
     * @throws \Exception
     */
    private function resolveSiteaccessAndGenerate(APILocation $location, array $parameters, int $referenceType): string
    {
        $parameters['siteaccess'] = $this->siteaccessResolver->resolveByLocation($location);

        $url = $this->generator->generate($location, $parameters, UrlGeneratorInterface::ABSOLUTE_URL);

        if ($referenceType === UrlGeneratorInterface::RELATIVE_PATH || $referenceType === UrlGeneratorInterface::ABSOLUTE_PATH) {
            $prefix = $this->requestContext->getScheme() . '://' . $this->requestContext->getHost();
            $prefixLength = mb_strlen($prefix);

            if (mb_strpos($url, $prefix) === 0) {
                return mb_substr($url, $prefixLength);
            }
        }

        return $url;
    }

    private function supportsObject($object): bool
    {
        return
            $object instanceof Content
            || $object instanceof ContentInfo
            || $object instanceof Location
            || $object instanceof APIContent
            || $object instanceof APIContentInfo
            || $object instanceof APILocation;
    }

    private function resolveLocation(string $name, array $parameters): APILocation
    {
        $routeObject = $parameters[RouteObjectInterface::ROUTE_OBJECT] ?? null;

        if (
            ($name === '' || $name === RouteObjectInterface::OBJECT_BASED_ROUTE_NAME)
            && $this->supportsObject($routeObject)
        ) {
            return $this->resolveLocationFromRouteObject($routeObject);
        }

        if ($name !== CoreUrlAliasRouter::URL_ALIAS_ROUTE_NAME) {
            throw new RouteNotFoundException('Pass to the next router');
        }

        $object = $parameters['location'] ?? null;

        if ($object instanceof Location) {
            return $object->innerLocation;
        }

        if ($object instanceof APILocation) {
            return $object;
        }

        if (isset($parameters['locationId'])) {
            return $this->repository->sudo(
                fn (): APILocation => $this->repository->getLocationService()->loadLocation($parameters['locationId'], []),
            );
        }

        $object = $parameters['content'] ?? null;

        if ($object instanceof Content) {
            return $this->checkContentLocation($object->mainLocation->innerLocation);
        }

        if ($object instanceof ContentInfo && $object->mainLocation) {
            return $this->checkContentLocation($object->mainLocation->innerLocation);
        }

        if ($object instanceof APIContent && $object->contentInfo->mainLocationId) {
            return $this->checkContentLocation($object->contentInfo->getMainLocation());
        }

        if ($object instanceof APIContentInfo && $object->mainLocationId) {
            return $this->checkContentLocation($object->getMainLocation());
        }

        if (isset($parameters['contentId'])) {
            return $this->repository->sudo(
                function () use ($parameters): APILocation {
                    $contentInfo = $this->repository->getContentService()->loadContentInfo($parameters['contentId']);

                    return $this->repository->getLocationService()->loadLocation($contentInfo->mainLocationId, []);
                },
            );
        }

        throw new RuntimeException('Could not resolve Location from the given parameters');
    }

    private function resolveLocationFromRouteObject($object): APILocation
    {
        if ($object instanceof Location) {
            return $object->innerLocation;
        }

        if ($object instanceof APILocation) {
            return $object;
        }

        if ($object instanceof Content) {
            return $this->checkContentLocation($object->mainLocation->innerLocation);
        }

        if ($object instanceof ContentInfo && $object->mainLocation) {
            return $this->checkContentLocation($object->mainLocation->innerLocation);
        }

        if ($object instanceof APIContent && $object->contentInfo->mainLocationId) {
            return $this->checkContentLocation($object->contentInfo->getMainLocation());
        }

        if ($object instanceof APIContentInfo && $object->mainLocationId) {
            return $this->checkContentLocation($object->getMainLocation());
        }

        throw new RuntimeException('Could not resolve Location for the given object');
    }

    private function checkContentLocation(?APILocation $location): APILocation
    {
        if ($location === null) {
            throw new LogicException(
                'Cannot generate an UrlAlias route for Content without the main Location',
            );
        }

        return $location;
    }
}
