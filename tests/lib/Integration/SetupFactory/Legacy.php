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

//    /**
//     * {@inheritdoc}
//     *
//     * @throws \Exception
//     */
//    public function getServiceContainer(): ServiceContainer
//    {
//        if (self::$serviceContainer === null) {
//            $config = include __DIR__ . '/../../../../vendor/ezsystems/ezplatform-kernel/config.php';
// //            $installDir = $config['install_dir'];
//            $installDir = self::getInstallationDir();
//
//            /** @var \Symfony\Component\DependencyInjection\ContainerBuilder $containerBuilder */
//            $containerBuilder = include $config['container_builder_path'];
//            $containerBuilder->addCompilerPass(new RelationResolverRegistrationPass());
//            $containerBuilder->addCompilerPass(new FieldRegistryPass());
//
//            /* @var \Symfony\Component\DependencyInjection\Loader\YamlFileLoader $loader */
//            $loader->load('search_engines/legacy.yml');
//            $loader->load('tests/integration_legacy.yml');
//
//            $settingsPath = __DIR__ . '/../../../../lib/Resources/config/';
//            $siteLoader = new YamlFileLoader($containerBuilder, new FileLocator($settingsPath));
//            $siteLoader->load('services.yml');
//
//            $settingsPath = __DIR__ . '/../../../../tests/lib/Integration/Resources/config/';
//            $siteLoader = new YamlFileLoader($containerBuilder, new FileLocator($settingsPath));
//            $siteLoader->load('legacy.yml');
//
//            $settingsPath = __DIR__ . '/../../../../vendor/netgen/ezplatform-search-extra/lib/Resources/config/search/';
//            $searchExtraLoader = new YamlFileLoader($containerBuilder, new FileLocator($settingsPath));
//            $searchExtraLoader->load('common.yml');
//            $searchExtraLoader->load('legacy.yml');
//
//            $containerBuilder->setParameter(
//                'legacy_dsn',
//                self::$dsn
//            );
//
//            $containerBuilder->setParameter(
//                'io_root_dir',
//                self::$ioRootDir . '/' . $containerBuilder->getParameter('storage_dir')
//            );
//
//            self::$serviceContainer = new ServiceContainer(
//                $containerBuilder,
//                $installDir,
//                $config['cache_dir'],
//                true,
//                true
//            );
//        }
//
//        return self::$serviceContainer;
//    }
}
