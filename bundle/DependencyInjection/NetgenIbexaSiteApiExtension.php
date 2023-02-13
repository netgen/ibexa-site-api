<?php

declare(strict_types=1);

namespace Netgen\Bundle\IbexaSiteApiBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\Yaml\Yaml;

use function array_keys;
use function file_get_contents;
use function in_array;

class NetgenIbexaSiteApiExtension extends Extension implements PrependExtensionInterface
{
    public function getAlias(): string
    {
        return 'netgen_ibexa_site_api';
    }

    /**
     * {@inheritdoc}
     *
     * @return \Netgen\Bundle\IbexaSiteApiBundle\DependencyInjection\Configuration
     */
    public function getConfiguration(array $config, ContainerBuilder $container): Configuration
    {
        return new Configuration($this->getAlias());
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Exception
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $activatedBundles = array_keys($container->getParameter('kernel.bundles'));

        $configuration = $this->getConfiguration($configs, $container);
        $config = $this->processConfiguration($configuration, $configs);

        $coreFileLocator = new FileLocator(__DIR__ . '/../../lib/Resources/config');
        $coreLoader = new Loader\YamlFileLoader($container, $coreFileLocator);
        $coreLoader->load('services.yaml');

        if (in_array('NetgenTagsBundle', $activatedBundles, true)) {
            $coreLoader->load('query_types/netgen_tags_dependant.yaml');
        }

        $fileLocator = new FileLocator(__DIR__ . '/../Resources/config');
        $loader = new Loader\YamlFileLoader($container, $fileLocator);
        $loader->load('services.yaml');
    }

    public function prepend(ContainerBuilder $container): void
    {
        $configFile = __DIR__ . '/../Resources/config/ibexa.yaml';
        $config = Yaml::parse(file_get_contents($configFile));
        $container->addResource(new FileResource($configFile));

        $container->prependExtensionConfig('ibexa', $config);
    }
}
