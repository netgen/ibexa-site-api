<?php

declare(strict_types=1);

namespace Netgen\IbexaSiteApi\Tests\Integration\SetupFactory;

use Ibexa\Contracts\Core\Test\Repository\SetupFactory\Legacy as CoreLegacySetupFactory;
use Ibexa\Core\Base\Container\Compiler\Search\FieldRegistryPass;
use Netgen\Bundle\IbexaSiteApiBundle\DependencyInjection\Compiler\RelationResolverRegistrationPass;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

/**
 * Used to set up the infrastructure for Repository Public API integration tests,
 * based on Repository with Legacy Storage Engine implementation.
 */
class Legacy extends CoreLegacySetupFactory
{
    /**
     * @throws \Exception
     */
    protected function externalBuildContainer(ContainerBuilder $containerBuilder): void
    {
        $containerBuilder->addCompilerPass(new RelationResolverRegistrationPass());
        $containerBuilder->addCompilerPass(new FieldRegistryPass());

        $settingsPath = __DIR__ . '/../../../../lib/Resources/config/';
        $loader = new YamlFileLoader($containerBuilder, new FileLocator($settingsPath));
        $loader->load('services.yaml');

        $searchExtraSettingsPath = __DIR__ . '/../../../../vendor/netgen/ibexa-search-extra/lib/Resources/config/search/';
        $searchExtraLoader = new YamlFileLoader($containerBuilder, new FileLocator($searchExtraSettingsPath));
        $searchExtraLoader->load('common.yaml');
        $searchExtraLoader->load('legacy.yaml');

        $testSettingsPath = __DIR__ . '/../Resources/config/';
        $testLoader = new YamlFileLoader($containerBuilder, new FileLocator($testSettingsPath));
        $testLoader->load('legacy.yaml');
    }
}
