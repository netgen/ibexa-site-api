<?php

declare(strict_types=1);

namespace Netgen\Bundle\IbexaSiteApiBundle;

use Netgen\Bundle\IbexaSiteApiBundle\DependencyInjection\Compiler\InvalidRedirectConfigurationListenerPass;
use Netgen\Bundle\IbexaSiteApiBundle\DependencyInjection\Compiler\NamedObjectExpressionFunctionProviderPass;
use Netgen\Bundle\IbexaSiteApiBundle\DependencyInjection\Compiler\PreviewControllerOverridePass;
use Netgen\Bundle\IbexaSiteApiBundle\DependencyInjection\Compiler\QueryTypeExpressionFunctionProviderPass;
use Netgen\Bundle\IbexaSiteApiBundle\DependencyInjection\Compiler\RedirectExpressionFunctionProviderPass;
use Netgen\Bundle\IbexaSiteApiBundle\DependencyInjection\Compiler\RelationResolverRegistrationPass;
use Netgen\Bundle\IbexaSiteApiBundle\DependencyInjection\Compiler\UrlAliasGeneratorOverridePass;
use Netgen\Bundle\IbexaSiteApiBundle\DependencyInjection\Compiler\UrlAliasRouterOverridePass;
use Netgen\Bundle\IbexaSiteApiBundle\DependencyInjection\Compiler\VarDumperPass;
use Netgen\Bundle\IbexaSiteApiBundle\DependencyInjection\Compiler\ViewBuilderRegistrationPass;
use Netgen\Bundle\IbexaSiteApiBundle\DependencyInjection\Configuration\Parser\ContentView;
use Netgen\Bundle\IbexaSiteApiBundle\DependencyInjection\Configuration\Parser\SiteApi;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class NetgenIbexaSiteApiBundle extends Bundle
{
    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        $container->addCompilerPass(new UrlAliasGeneratorOverridePass());
        $container->addCompilerPass(new UrlAliasRouterOverridePass());
        $container->addCompilerPass(new InvalidRedirectConfigurationListenerPass());
        $container->addCompilerPass(new NamedObjectExpressionFunctionProviderPass());
        $container->addCompilerPass(new PreviewControllerOverridePass());
        $container->addCompilerPass(new QueryTypeExpressionFunctionProviderPass());
        $container->addCompilerPass(new RedirectExpressionFunctionProviderPass());
        $container->addCompilerPass(new RelationResolverRegistrationPass());
        $container->addCompilerPass(new VarDumperPass());
        $container->addCompilerPass(new ViewBuilderRegistrationPass());

        /** @var \Ibexa\Bundle\Core\DependencyInjection\IbexaCoreExtension $coreExtension */
        $coreExtension = $container->getExtension('ibexa');

        $coreExtension->addConfigParser(new ContentView());
        $coreExtension->addConfigParser(new SiteApi());

        $coreExtension->addDefaultSettings(
            __DIR__ . '/Resources/config',
            [
                'ibexa_default_settings.yaml',
            ],
        );
    }
}
