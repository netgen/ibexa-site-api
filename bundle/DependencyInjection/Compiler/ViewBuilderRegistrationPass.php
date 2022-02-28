<?php

declare(strict_types=1);

namespace Netgen\Bundle\IbexaSiteApiBundle\DependencyInjection\Compiler;

use Ibexa\Core\MVC\Symfony\View\Builder\ViewBuilderRegistry;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class ViewBuilderRegistrationPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        // todo check
        if (!$container->has(ViewBuilderRegistry::class)) {
            return;
        }

        $viewBuilderRegistry = $container->findDefinition(ViewBuilderRegistry::class);
        $contentViewBuilder = $container->findDefinition('netgen.ibexa_site_api.view_builder.content');

        $viewBuilderRegistry->addMethodCall(
            'addToRegistry',
            [[$contentViewBuilder]],
        );
    }
}
