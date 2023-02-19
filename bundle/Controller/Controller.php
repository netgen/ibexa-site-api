<?php /** @noinspection PhpUnused */

declare(strict_types=1);

namespace Netgen\Bundle\IbexaSiteApiBundle\Controller;

use Ibexa\Contracts\Core\Repository\Repository;
use Ibexa\Contracts\Core\SiteAccess\ConfigResolverInterface;
use Ibexa\Core\MVC\Symfony\Templating\GlobalHelper;
use Ibexa\Core\QueryType\QueryTypeRegistry;
use Netgen\Bundle\IbexaSiteApiBundle\NamedObject\Provider as NamedObjectProvider;
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
        $subscribedServices = [
            // Ibexa
            Repository::class,
            ConfigResolverInterface::class,
            QueryTypeRegistry::class,
            GlobalHelper::class,
            // Netgen
            ContentRenderer::class,
            FilterService::class,
            FindService::class,
            LoadService::class,
            NamedObjectProvider::class,
            RelationService::class,
            Site::class,
            Settings::class,
            ViewRenderer::class,
        ];

        return $subscribedServices + parent::getSubscribedServices();
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
        return $this->container->get(QueryTypeRegistry::class);
    }

    protected function getRepository(): Repository
    {
        return $this->container->get(Repository::class);
    }

    /**
     * Returns the general helper service, exposed in Twig templates as "ibexa" global variable.
     */
    protected function getGlobalHelper(): GlobalHelper
    {
        return $this->container->get(GlobalHelper::class);
    }

    protected function getSite(): Site
    {
        return $this->container->get(Site::class);
    }

    protected function getSiteSettings(): Settings
    {
        return $this->container->get(Settings::class);
    }

    protected function getConfigResolver(): ConfigResolverInterface
    {
        return $this->container->get(ConfigResolverInterface::class);
    }

    protected function getNamedObjectProvider(): NamedObjectProvider
    {
        return $this->container->get(NamedObjectProvider::class);
    }

    protected function getContentRenderer(): ContentRenderer
    {
        return $this->container->get(ContentRenderer::class);
    }

    protected function getViewRenderer(): ViewRenderer
    {
        return $this->container->get(ViewRenderer::class);
    }

    protected function getLoadService(): LoadService
    {
        return $this->container->get(LoadService::class);
    }

    protected function getFilterService(): FilterService
    {
        return $this->container->get(FilterService::class);
    }

    protected function getFindService(): FindService
    {
        return $this->container->get(FindService::class);
    }

    protected function getRelationService(): RelationService
    {
        return $this->container->get(RelationService::class);
    }
}
