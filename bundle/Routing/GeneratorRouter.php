<?php

declare(strict_types=1);

namespace Netgen\Bundle\IbexaSiteApiBundle\Routing;

use Ibexa\Core\MVC\Symfony\Routing\UrlAliasRouter as CoreUrlAliasRouter;
use Ibexa\Contracts\Core\Repository\ContentService;
use Ibexa\Contracts\Core\Repository\LocationService;
use Ibexa\Contracts\Core\Repository\Values\Content\Content as APIContent;
use Ibexa\Contracts\Core\Repository\Values\Content\ContentInfo as APIContentInfo;
use Ibexa\Contracts\Core\Repository\Values\Content\Location as APILocation;
use Ibexa\Contracts\Core\SiteAccess\ConfigResolverInterface;
use Ibexa\Core\MVC\Symfony\Routing\Generator\UrlAliasGenerator;
use Ibexa\Core\MVC\Symfony\SiteAccess;
use LogicException;
use Netgen\IbexaSiteApi\API\Values\Content;
use Netgen\IbexaSiteApi\API\Values\ContentInfo;
use Netgen\IbexaSiteApi\API\Values\Location;
use RuntimeException;
use Symfony\Cmf\Component\Routing\ChainedRouterInterface;
use Symfony\Cmf\Component\Routing\RouteObjectInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\Matcher\RequestMatcherInterface;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Route as SymfonyRoute;
use Symfony\Component\Routing\RouteCollection;
use function strlen;
use function strpos;
use function substr;

/**
 * @final
 *
 * @internal do not use directly; use @router instead to keep the chain routing mechanism
 */
class GeneratorRouter implements ChainedRouterInterface, RequestMatcherInterface
{
    private UrlAliasGenerator $generator;
    private CrossSiteaccessResolver $siteaccessResolver;
    private ConfigResolverInterface $configResolver;
    private LocationService $locationService;
    private ContentService $contentService;
    private RequestContext $requestContext;
    private SiteAccess $currentSiteaccess;

    public function __construct(
        UrlAliasGenerator $generator,
        CrossSiteaccessResolver $siteaccessResolver,
        LocationService $locationService,
        ContentService $contentService,
        RequestContext $requestContext
    ) {
        $this->generator = $generator;
        $this->siteaccessResolver = $siteaccessResolver;
        $this->locationService = $locationService;
        $this->contentService = $contentService;
        $this->requestContext = $requestContext;
    }

    public function setSiteaccess(SiteAccess $currentSiteAccess = null): void
    {
        $this->currentSiteaccess = $currentSiteAccess;
    }

    public function setConfigResolver(ConfigResolverInterface $configResolver): void
    {
        $this->configResolver = $configResolver;
    }

    /**
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException
     * @throws \Exception
     */
    public function generate(
        string $name,
        array $parameters = [],
        int $referenceType = UrlGeneratorInterface::ABSOLUTE_PATH
    ): string {
        $location = $this->resolveLocation($name, $parameters);

        unset(
            $parameters['location'],
            $parameters['locationId'],
            $parameters['content'],
            $parameters['contentInfo'],
            $parameters['contentId'],
            $parameters[RouteObjectInterface::ROUTE_OBJECT],
        );

        $isCrossSiteaccessRoutingEnabled = $this->configResolver->getParameter(
            'ng_site_api.cross_siteaccess_routing.enabled'
        );

        if (isset($parameters['siteaccess']) || !$isCrossSiteaccessRoutingEnabled) {
            return $this->generator->generate($location, $parameters, $referenceType);
        }

        return $this->crossSiteaccessGenerate($location, $parameters, $referenceType);
    }

    /**
     * @throws \Exception
     */
    private function crossSiteaccessGenerate(APILocation $location, array $parameters, int $referenceType): string
    {
        $siteaccessName = $this->siteaccessResolver->resolve($location);

        if ($siteaccessName === $this->currentSiteaccess->name) {
            return $this->generator->generate($location, $parameters, $referenceType);
        }

        $parameters['siteaccess'] = $siteaccessName;

        $url = $this->generator->generate($location, $parameters, UrlGeneratorInterface::ABSOLUTE_URL);

        if ($referenceType === UrlGeneratorInterface::RELATIVE_PATH) {
            $host = $this->requestContext->getHost();
            $hostLength = strlen($host);

            if (strpos($url, $host) === 0) {
                return substr($url, $hostLength);
            }
        }

        return $url;
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
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException
     */
    private function resolveLocation(string $name, array $parameters): APILocation
    {
        $routeObject = $parameters[RouteObjectInterface::ROUTE_OBJECT] ?? null;

        if ($routeObject !== null && $this->supportsObject($routeObject)) {
            return $this->resolveLocationFromRouteObject($routeObject);
        }

        if ($name !== CoreUrlAliasRouter::URL_ALIAS_ROUTE_NAME) {
            throw new ResourceNotFoundException('Pass to the next router');
        }

        $object = $parameters['location'] ?? null;

        if ($object instanceof Location) {
            return $object->innerLocation;
        }

        if ($object instanceof APILocation) {
            return $object;
        }

        if (isset($parameters['locationId'])) {
            return $this->locationService->loadLocation($parameters['locationId']);
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
            $contentInfo = $this->contentService->loadContentInfo($parameters['contentId']);

            return $this->checkContentLocation($contentInfo->getMainLocation());
        }

        throw new LogicException('Could not resolve Location from the given parameters');
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
                'Cannot generate an UrlAlias route for Content without the main Location'
            );
        }

        return $location;
    }
}
