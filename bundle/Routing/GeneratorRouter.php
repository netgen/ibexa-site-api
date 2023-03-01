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
use Ibexa\Core\MVC\Symfony\SiteAccess;
use LogicException;
use Netgen\Bundle\IbexaSiteApiBundle\Exception\SiteAccessResolver\SiteAccessMatchException;
use Netgen\Bundle\IbexaSiteApiBundle\SiteAccess\Resolver;
use Netgen\IbexaSiteApi\API\Values\Content;
use Netgen\IbexaSiteApi\API\Values\ContentInfo;
use Netgen\IbexaSiteApi\API\Values\Location;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
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
use function mb_strlen;
use function mb_substr;
use function sprintf;
use function str_starts_with;

/**
 * @final
 *
 * @internal do not use directly; use @router instead to keep the chain routing mechanism
 */
class GeneratorRouter implements ChainedRouterInterface, RequestMatcherInterface
{
    private SiteAccess $currentSiteaccess;

    public function __construct(
        private readonly Repository $repository,
        private readonly UrlAliasGenerator $generator,
        private readonly Resolver $siteaccessResolver,
        private readonly ConfigResolverInterface $configResolver,
        private RequestContext $requestContext,
        private readonly LoggerInterface $logger = new NullLogger(),
    ) {
    }

    /** @noinspection PhpUnused */
    public function setSiteaccess(?SiteAccess $currentSiteAccess = null): void
    {
        $this->currentSiteaccess = $currentSiteAccess;
    }

    public function generate(
        string $name,
        array $parameters = [],
        int $referenceType = UrlGeneratorInterface::ABSOLUTE_PATH,
    ): string {
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

        $isSiteApiPrimaryContentView = $this->configResolver->getParameter(
            'ng_site_api.site_api_is_primary_content_view',
        );

        if (isset($parameters['siteaccess']) || !$isSiteApiPrimaryContentView) {
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
            return sprintf('Route with key "%s"', $name->getRouteKey());
        }

        if ($name instanceof SymfonyRoute) {
            return sprintf('Route with pattern "%s"', $name->getPath());
        }

        return $name;
    }

    private function resolveSiteaccessAndGenerate(APILocation $location, array $parameters, int $referenceType): string
    {
        try {
            $siteaccess = $this->siteaccessResolver->resolveByLocation($location);
        } catch (SiteAccessMatchException $exception) {
            $this->logger->error(
                sprintf(
                    'Could not resolve siteaccess for Location #%s, falling back to the current siteaccess: %s',
                    $location->id,
                    $exception->getMessage(),
                ),
            );

            $siteaccess = $this->currentSiteaccess->name;
        }

        if ($siteaccess === $this->currentSiteaccess->name) {
            return $this->generator->generate($location, $parameters, $referenceType);
        }

        $parameters['siteaccess'] = $siteaccess;

        $url = $this->generator->generate(
            $location,
            $parameters,
            UrlGeneratorInterface::ABSOLUTE_URL,
        );

        if ($referenceType === UrlGeneratorInterface::RELATIVE_PATH || $referenceType === UrlGeneratorInterface::ABSOLUTE_PATH) {
            $prefix = sprintf('%s://%s', $this->requestContext->getScheme(), $this->requestContext->getHost());
            $prefixLength = mb_strlen($prefix);

            if (str_starts_with($url, $prefix)) {
                return mb_substr($url, $prefixLength);
            }
        }

        return $url;
    }

    private function supportsObject($object): bool
    {
        return $object instanceof Content
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
            return $this->loadLocationByContentId($parameters['contentId']);
        }

        throw new RuntimeException('Could not resolve Location from the given parameters');
    }

    private function loadLocationByContentId(int $contentId): APILocation
    {
        return $this->repository->sudo(
            function () use ($contentId): APILocation {
                $contentInfo = $this->repository->getContentService()->loadContentInfo($contentId);

                if ($contentInfo->mainLocationId === null) {
                    throw new LogicException(
                        'Cannot generate an UrlAlias route for Content without the main Location',
                    );
                }

                return $this->repository->getLocationService()->loadLocation($contentInfo->mainLocationId, []);
            },
        );
    }

    private function resolveLocationFromRouteObject(mixed $object): APILocation
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
        return $location ?? throw new LogicException(
            'Cannot generate an UrlAlias route for Content without the main Location',
        );
    }
}
