<?php

declare(strict_types=1);

namespace Netgen\Bundle\IbexaSiteApiBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

final class InvalidRedirectConfigurationListenerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if (!$container->hasDefinition('netgen.ibexa_site_api.event_listener.invalid_redirect_configuration')) {
            return;
        }

        if ($container->getParameter('kernel.debug') === false) {
            return;
        }

        $container->removeDefinition('netgen.ibexa_site_api.event_listener.invalid_redirect_configuration');
    }
}
