<?php

declare(strict_types=1);

namespace Netgen\Bundle\IbexaSiteApiBundle\DependencyInjection\Compiler;

use Netgen\Bundle\IbexaSiteApiBundle\Controller\PreviewController;
use Symfony\Component\DependencyInjection\Alias;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class PreviewControllerOverridePass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        $corePreviewControllerServiceId = '@Ibexa\Core\MVC\Symfony\Controller\Content\PreviewController';

        if (!$container->hasDefinition($corePreviewControllerServiceId)) {
            return;
        }

        $container
            ->findDefinition($corePreviewControllerServiceId)
            ->setClass(PreviewController::class)
            ->addMethodCall(
                'setConfigResolver',
                [new Reference('ibexa.config.resolver')]
            )
            ->addMethodCall(
                'setSite',
                [new Reference('netgen.ibexa_site_api.core.site')]
            );

        // todo check
        // Redefine the alias as it seems to be mangled in some cases
        // See https://github.com/netgen/ezplatform-site-api/pull/168
        $container->setAlias(
            'ibexa.controller.content.preview',
            new Alias($corePreviewControllerServiceId, true)
        );
    }
}
