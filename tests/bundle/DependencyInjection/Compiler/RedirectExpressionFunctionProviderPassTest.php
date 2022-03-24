<?php

declare(strict_types=1);

namespace Netgen\Bundle\IbexaSiteApiBundle\Tests\DependencyInjection\Compiler;

use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractCompilerPassTestCase;
use Netgen\Bundle\IbexaSiteApiBundle\DependencyInjection\Compiler\RedirectExpressionFunctionProviderPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

/**
 * @internal
 */
final class RedirectExpressionFunctionProviderPassTest extends AbstractCompilerPassTestCase
{
    protected const ExpressionLanguageId = 'netgen.ibexa_site_api.redirect.expression_language';
    protected const ExpressionFunctionProviderTag = 'netgen.ibexa_site_api.redirect.expression_function_provider';

    protected function setUp(): void
    {
        parent::setUp();

        $this->setDefinition(
            self::ExpressionLanguageId,
            new Definition(),
        );
    }

    public function testRegisterProvider(): void
    {
        $serviceId = 'expression_function_provider_service_id';
        $definition = new Definition();
        $definition->addTag(self::ExpressionFunctionProviderTag);
        $this->setDefinition($serviceId, $definition);

        $this->compile();

        $this->assertContainerBuilderHasServiceDefinitionWithMethodCall(
            self::ExpressionLanguageId,
            'registerProvider',
            [$serviceId],
        );
    }

    protected function registerCompilerPass(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new RedirectExpressionFunctionProviderPass());
    }
}
