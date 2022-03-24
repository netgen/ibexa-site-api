<?php

declare(strict_types=1);

namespace Netgen\Bundle\IbexaSiteApiBundle\DependencyInjection\Compiler;

use LogicException;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

/**
 * This compiler pass will register field type relation resolver plugins.
 */
final class RelationResolverRegistrationPass implements CompilerPassInterface
{
    /**
     * Service ID of the resolver registry.
     *
     * @see \Netgen\IbexaSiteApi\Core\Site\Plugins\FieldType\RelationResolver\Registry
     *
     * @var string
     */
    private string $resolverRegistryId = 'netgen.ibexa_site_api.plugins.field_type.relation_resolver.registry';

    /**
     * Service tag used for field type relation resolvers.
     *
     * @see \Netgen\IbexaSiteApi\Core\Site\Plugins\FieldType\RelationResolver\Resolver
     *
     * @var string
     */
    private string $resolverTag = 'netgen.ibexa_site_api.plugins.field_type.relation_resolver';

    public function process(ContainerBuilder $container): void
    {
        if (!$container->has($this->resolverRegistryId)) {
            return;
        }

        $resolverRegistryDefinition = $container->getDefinition($this->resolverRegistryId);

        $resolvers = $container->findTaggedServiceIds($this->resolverTag);

        foreach ($resolvers as $id => $attributes) {
            /* @var array $attributes */
            $this->registerResolver($resolverRegistryDefinition, $id, $attributes);
        }
    }

    /**
     * Add method call to register resolver with given $id with resolver registry.
     *
     * @throws \LogicException
     */
    private function registerResolver(Definition $resolverRegistryDefinition, string $id, array $attributes): void
    {
        foreach ($attributes as $attribute) {
            if (!isset($attribute['identifier'])) {
                throw new LogicException(
                    "'{$this->resolverTag}' service tag needs an 'identifier' attribute to identify the field type",
                );
            }

            $resolverRegistryDefinition->addMethodCall(
                'register',
                [
                    $attribute['identifier'],
                    new Reference($id),
                ],
            );
        }
    }
}
