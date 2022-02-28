<?php

declare(strict_types=1);

namespace Netgen\Bundle\IbexaSiteApiBundle\Core\Site;

use Ibexa\Contracts\Core\Repository\Exceptions\PropertyNotFoundException;
use Ibexa\Contracts\Core\Repository\Exceptions\PropertyReadOnlyException;
use Ibexa\Contracts\Core\SiteAccess\ConfigResolverInterface;
use Netgen\IbexaSiteApi\API\Settings as BaseSettings;

final class Settings extends BaseSettings
{
    private ConfigResolverInterface $configResolver;

    public function __construct(ConfigResolverInterface $configResolver)
    {
        $this->configResolver = $configResolver;
    }

    /**
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\PropertyNotFoundException
     */
    public function __get(string $property)
    {
        switch ($property) {
            case 'prioritizedLanguages':
                return $this->configResolver->getParameter('languages');

            case 'useAlwaysAvailable':
                return $this->configResolver->getParameter('ng_site_api.use_always_available_fallback');

            case 'rootLocationId':
                return $this->configResolver->getParameter('content.tree_root.location_id');

            case 'showHiddenItems':
                return $this->configResolver->getParameter('ng_site_api.show_hidden_items');

            case 'failOnMissingField':
                return $this->configResolver->getParameter('ng_site_api.fail_on_missing_field');
        }

        throw new PropertyNotFoundException($property, __CLASS__);
    }

    /**
     * @param mixed $value
     *
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\PropertyReadOnlyException
     */
    public function __set(string $property, $value): void
    {
        throw new PropertyReadOnlyException($property, __CLASS__);
    }

    /**
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\PropertyNotFoundException
     */
    public function __isset(string $property): bool
    {
        switch ($property) {
            case 'prioritizedLanguages':
            case 'useAlwaysAvailable':
            case 'rootLocationId':
            case 'showHiddenItems':
            case 'failOnMissingField':
                return true;
        }

        throw new PropertyNotFoundException($property, __CLASS__);
    }
}
