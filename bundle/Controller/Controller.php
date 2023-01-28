<?php

declare(strict_types=1);

namespace Netgen\Bundle\IbexaSiteApiBundle\Controller;

use Ibexa\Contracts\Core\Repository\Repository;
use Ibexa\Contracts\Core\SiteAccess\ConfigResolverInterface;
use Ibexa\Core\MVC\Symfony\Templating\GlobalHelper;
use Ibexa\Core\QueryType\ArrayQueryTypeRegistry;
use Ibexa\Core\QueryType\QueryTypeRegistry;
use Netgen\Bundle\IbexaSiteApiBundle\NamedObject\Provider;
use Netgen\Bundle\IbexaSiteApiBundle\View\ContentRenderer;
use Netgen\Bundle\IbexaSiteApiBundle\View\ViewRenderer;
use Netgen\IbexaSiteApi\API\FilterService;
use Netgen\IbexaSiteApi\API\FindService;
use Netgen\IbexaSiteApi\API\LoadService;
use Netgen\IbexaSiteApi\API\RelationService;
use Netgen\IbexaSiteApi\API\Settings;
use Netgen\IbexaSiteApi\API\Site;
use Netgen\IbexaSiteApi\API\Values\Location;
use Netgen\IbexaSiteApi\Core\Traits\PagerfantaTrait;
use Netgen\IbexaSiteApi\Core\Traits\SearchResultExtractorTrait;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

abstract class Controller extends AbstractController
{
    use PagerfantaTrait;
    use SearchResultExtractorTrait;

    public static function getSubscribedServices(): array
    {
        return [
            'ibexa.api.repository' => Repository::class,
            'ibexa.config.resolver' => ConfigResolverInterface::class,
            'ibexa.templating.global_helper' => GlobalHelper::class,
            'netgen.ibexa_site_api.content_renderer' => ContentRenderer::class,
            'netgen.ibexa_site_api.filter_service' => FilterService::class,
            'netgen.ibexa_site_api.find_service' => FindService::class,
            'netgen.ibexa_site_api.load_service' => LoadService::class,
            'netgen.ibexa_site_api.named_object.provider' => Provider::class,
            'netgen.ibexa_site_api.relation_service' => RelationService::class,
            'netgen.ibexa_site_api.site' => Site::class,
            'netgen.ibexa_site_api.settings' => Settings::class,
            'netgen.ibexa_site_api.view_renderer' => ViewRenderer::class,
            QueryTypeRegistry::class => QueryTypeRegistry::class,
        ] + parent::getSubscribedServices();
    }

    /**
     * Returns the root Location object for current siteaccess configuration.
     */
    protected function getRootLocation(): Location
    {
        return $this->getLoadService()->loadLocation(
            $this->getSiteSettings()->rootLocationId,
        );
    }

    protected function getQueryTypeRegistry(): QueryTypeRegistry
    {
        return $this->container->get(ArrayQueryTypeRegistry::class);
    }

    protected function getRepository(): Repository
    {
        return $this->container->get('ibexa.api.repository');
    }

    /**
     * Returns the general helper service, exposed in Twig templates as "ibexa" global variable.
     */
    protected function getGlobalHelper(): GlobalHelper
    {
        return $this->container->get('ibexa.templating.global_helper');
    }

    protected function getSite(): Site
    {
        return $this->container->get('netgen.ibexa_site_api.site');
    }

    protected function getSiteSettings(): Settings
    {
        return $this->container->get('netgen.ibexa_site_api.settings');
    }

    protected function getConfigResolver(): ConfigResolverInterface
    {
        return $this->container->get('ibexa.config.resolver');
    }

    protected function getNamedObjectProvider(): Provider
    {
        return $this->container->get('netgen.ibexa_site_api.named_object.provider');
    }

    protected function getContentRenderer(): ContentRenderer
    {
        return $this->container->get('netgen.ibexa_site_api.content_renderer');
    }

    protected function getViewRenderer(): ViewRenderer
    {
        return $this->container->get('netgen.ibexa_site_api.view_renderer');
    }

    protected function getLoadService(): LoadService
    {
        return $this->container->get('netgen.ibexa_site_api.load_service');
    }

    protected function getFilterService(): FilterService
    {
        return $this->container->get('netgen.ibexa_site_api.filter_service');
    }

    protected function getFindService(): FindService
    {
        return $this->container->get('netgen.ibexa_site_api.find_service');
    }

    protected function getRelationService(): RelationService
    {
        return $this->container->get('netgen.ibexa_site_api.relation_service');
    }
}
