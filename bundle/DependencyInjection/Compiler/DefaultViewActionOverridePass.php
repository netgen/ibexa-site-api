<?php

declare(strict_types=1);

namespace Netgen\Bundle\IbexaSiteApiBundle\DependencyInjection\Compiler;

use Ibexa\Bundle\Core\Routing\UrlAliasRouter as IbexaUrlAliasRouter;
use Netgen\Bundle\IbexaSiteApiBundle\Routing\UrlAliasRouter;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class DefaultViewActionOverridePass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if (!$container->hasDefinition(IbexaUrlAliasRouter::class)) {
            return;
        }

        $container
            ->findDefinition(IbexaUrlAliasRouter::class)
            ->setClass(UrlAliasRouter::class);
    }
}
