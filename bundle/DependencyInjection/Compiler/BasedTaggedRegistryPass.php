<?php

declare(strict_types=1);

namespace Netgen\Bundle\IbexaSiteApiBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use function array_keys;

abstract class BasedTaggedRegistryPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if (!$container->has(static::RegistryId)) {
            return;
        }

        $registryDefinition = $container->getDefinition(static::RegistryId);
        $registrees = $container->findTaggedServiceIds(static::RegistreeTag);

        foreach (array_keys($registrees) as $registreeId) {
            $registryDefinition->addMethodCall(
                static::RegisterMethod,
                [new Reference($registreeId)],
            );
        }
    }
}
