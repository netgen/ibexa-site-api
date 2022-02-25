<?php

declare(strict_types=1);

namespace Netgen\Bundle\IbexaSiteApiBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class ViewBuilderRegistrationPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        // todo check
        if (!$container->has('@Ibexa\Core\MVC\Symfony\View\Builder\ViewBuilderRegistry')) {
            return;
        }

        $viewBuilderRegistry = $container->findDefinition('@Ibexa\Core\MVC\Symfony\View\Builder\ViewBuilderRegistry');
        $contentViewBuilder = $container->findDefinition('netgen.ibexa_site_api.view_builder.content');

        $viewBuilderRegistry->addMethodCall(
            'addToRegistry',
            [[$contentViewBuilder]]
        );
    }
}
