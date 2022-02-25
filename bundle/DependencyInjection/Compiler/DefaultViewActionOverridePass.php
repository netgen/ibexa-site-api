<?php

declare(strict_types=1);

namespace Netgen\Bundle\IbexaSiteApiBundle\DependencyInjection\Compiler;

use Netgen\Bundle\IbexaSiteApiBundle\Routing\UrlAliasRouter;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class DefaultViewActionOverridePass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if (!$container->hasDefinition('@Ibexa\Bundle\Core\Routing\UrlAliasRouter')) {
            return;
        }

        $container
            ->findDefinition('@Ibexa\Bundle\Core\Routing\UrlAliasRouter')
            ->setClass(UrlAliasRouter::class);
    }
}
