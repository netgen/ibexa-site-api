<?php

declare(strict_types=1);

namespace Netgen\Bundle\IbexaSiteApiBundle\Controller;

use Ibexa\Contracts\Core\Repository\Repository;
use Ibexa\Contracts\Core\SiteAccess\ConfigResolverInterface;
use Ibexa\Core\MVC\Symfony\Templating\GlobalHelper;
use Ibexa\Core\QueryType\ArrayQueryTypeRegistry;
use Ibexa\Core\QueryType\QueryTypeRegistry;
use Netgen\Bundle\IbexaSiteApiBundle\NamedObject\Provider;
use Netgen\IbexaSiteApi\API\Site;
use Netgen\IbexaSiteApi\API\Values\Location;
use Netgen\IbexaSiteApi\Core\Traits\PagerfantaTrait;
use Netgen\IbexaSiteApi\Core\Traits\SearchResultExtractorTrait;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

abstract class Controller extends AbstractController
{
    use PagerfantaTrait;
    use SearchResultExtractorTrait;

    /**
     * Returns the root Location object for current siteaccess configuration.
     *
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException
     * @throws \Netgen\IbexaSiteApi\API\Exceptions\TranslationNotMatchedException
     */
    public function getRootLocation(): Location
    {
        return $this->getSite()->getLoadService()->loadLocation(
            $this->getSite()->getSettings()->rootLocationId,
        );
    }

    public function getQueryTypeRegistry(): QueryTypeRegistry
    {
        return $this->container->get(ArrayQueryTypeRegistry::class);
    }

    public function getRepository(): Repository
    {
        return $this->container->get('ibexa.api.repository');
    }

    /**
     * Returns the general helper service, exposed in Twig templates as "ibexa" global variable.
     */
    public function getGlobalHelper(): GlobalHelper
    {
        return $this->container->get('ibexa.templating.global_helper');
    }

    public static function getSubscribedServices(): array
    {
        return [
            'netgen.ibexa_site_api.site' => Site::class,
            'netgen.ibexa_site_api.named_object_provider' => Provider::class,
            QueryTypeRegistry::class => QueryTypeRegistry::class,
            'ibexa.api.repository' => Repository::class,
            'ibexa.templating.global_helper' => GlobalHelper::class,
            'ibexa.config.resolver' => ConfigResolverInterface::class,
        ] + parent::getSubscribedServices();
    }

    protected function getSite(): Site
    {
        return $this->container->get('netgen.ibexa_site_api.site');
    }

    protected function getConfigResolver(): ConfigResolverInterface
    {
        return $this->container->get('ibexa.config.resolver');
    }

    protected function getNamedObjectProvider(): Provider
    {
        return $this->container->get('netgen.ibexa_site_api.named_object_provider');
    }
}
