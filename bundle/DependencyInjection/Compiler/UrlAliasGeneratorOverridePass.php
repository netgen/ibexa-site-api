<?php

declare(strict_types=1);

namespace Netgen\Bundle\IbexaSiteApiBundle\DependencyInjection\Compiler;

use Ibexa\Core\MVC\Symfony\Routing\Generator\UrlAliasGenerator as IbexaUrlAliasGenerator;
use Netgen\Bundle\IbexaSiteApiBundle\Routing\UrlAliasGenerator as SiteApiUrlAliasGenerator;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class UrlAliasGeneratorOverridePass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if (!$container->hasDefinition(IbexaUrlAliasGenerator::class)) {
            return;
        }

        $container
            ->findDefinition(IbexaUrlAliasGenerator::class)
            ->setClass(SiteApiUrlAliasGenerator::class);
    }
}
