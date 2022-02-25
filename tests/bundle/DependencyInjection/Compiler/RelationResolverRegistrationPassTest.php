<?php

declare(strict_types=1);

namespace Netgen\Bundle\IbexaSiteApiBundle\Tests\DependencyInjection\Compiler;

use LogicException;
use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractCompilerPassTestCase;
use Netgen\Bundle\IbexaSiteApiBundle\DependencyInjection\Compiler\RelationResolverRegistrationPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

/**
 * @internal
 */
final class RelationResolverRegistrationPassTest extends AbstractCompilerPassTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->setDefinition(
            'netgen.ibexa_site_api.plugins.field_type.relation_resolver.registry',
            new Definition()
        );
    }

    public function testRegisterResolver(): void
    {
        $fieldTypeIdentifier = 'field_type_identifier';
        $serviceId = 'service_id';
        $definition = new Definition();
        $definition->addTag(
            'netgen.ibexa_site_api.plugins.field_type.relation_resolver',
            ['identifier' => $fieldTypeIdentifier]
        );
        $this->setDefinition($serviceId, $definition);

        $this->compile();

        $this->assertContainerBuilderHasServiceDefinitionWithMethodCall(
            'netgen.ibexa_site_api.plugins.field_type.relation_resolver.registry',
            'register',
            [$fieldTypeIdentifier, $serviceId]
        );
    }

    public function testRegisterResolverWithoutIdentifier(): void
    {
        $this->expectException(LogicException::class);

        $serviceId = 'service_id';
        $definition = new Definition();
        $definition->addTag('netgen.ibexa_site_api.plugins.field_type.relation_resolver');
        $this->setDefinition($serviceId, $definition);

        $this->compile();
    }

    protected function registerCompilerPass(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new RelationResolverRegistrationPass());
    }
}
