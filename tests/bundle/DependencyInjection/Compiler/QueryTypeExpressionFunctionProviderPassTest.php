<?php

declare(strict_types=1);

namespace Netgen\Bundle\IbexaSiteApiBundle\Tests\DependencyInjection\Compiler;

use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractCompilerPassTestCase;
use Netgen\Bundle\IbexaSiteApiBundle\DependencyInjection\Compiler\QueryTypeExpressionFunctionProviderPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

/**
 * @internal
 */
final class QueryTypeExpressionFunctionProviderPassTest extends AbstractCompilerPassTestCase
{
    protected const QueryTypeExpressionLanguageId = 'netgen.ibexa_site_api.query_type.expression_language';
    protected const QueryTypeExpressionFunctionProviderTag = 'netgen.ibexa_site_api.query_type.expression_function_provider';

    protected function setUp(): void
    {
        parent::setUp();

        $this->setDefinition(
            self::QueryTypeExpressionLanguageId,
            new Definition(),
        );
    }

    public function testRegisterProvider(): void
    {
        $serviceId = 'expression_function_provider_service_id';
        $definition = new Definition();
        $definition->addTag(self::QueryTypeExpressionFunctionProviderTag);
        $this->setDefinition($serviceId, $definition);

        $this->compile();

        $this->assertContainerBuilderHasServiceDefinitionWithMethodCall(
            self::QueryTypeExpressionLanguageId,
            'registerProvider',
            [$serviceId],
        );
    }

    protected function registerCompilerPass(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new QueryTypeExpressionFunctionProviderPass());
    }
}
