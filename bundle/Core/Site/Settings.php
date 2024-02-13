<?php

declare(strict_types=1);

namespace Netgen\Bundle\IbexaSiteApiBundle\Core\Site;

use Ibexa\Contracts\Core\Repository\Exceptions\PropertyNotFoundException;
use Ibexa\Contracts\Core\Repository\Exceptions\PropertyReadOnlyException;
use Ibexa\Contracts\Core\SiteAccess\ConfigResolverInterface;
use Netgen\IbexaSiteApi\API\Settings as BaseSettings;

/**
 * Site Settings implementation using the current siteaccess configuration.
 */
final class Settings extends BaseSettings
{
    public function __construct(
        private readonly ConfigResolverInterface $configResolver,
    ) {}

    /**
     * @return bool|int|string[]
     *
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\PropertyNotFoundException
     */
    public function __get(string $property)
    {
        return match ($property) {
            'prioritizedLanguages' => $this->configResolver->getParameter('languages'),
            'useAlwaysAvailable' => $this->configResolver->getParameter('ng_site_api.use_always_available_fallback'),
            'rootLocationId' => $this->configResolver->getParameter('content.tree_root.location_id'),
            'showHiddenItems' => $this->configResolver->getParameter('ng_site_api.show_hidden_items'),
            'failOnMissingField' => $this->configResolver->getParameter('ng_site_api.fail_on_missing_field'),
            default => throw new PropertyNotFoundException($property, self::class),
        };
    }

    /**
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\PropertyReadOnlyException
     */
    public function __set(string $property, mixed $value): void
    {
        throw new PropertyReadOnlyException($property, self::class);
    }

    /**
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\PropertyNotFoundException
     */
    public function __isset(string $property): bool
    {
        return match ($property) {
            'prioritizedLanguages',
            'useAlwaysAvailable',
            'rootLocationId',
            'showHiddenItems',
            'failOnMissingField' => true,
            default => throw new PropertyNotFoundException($property, self::class),
        };
    }
}
