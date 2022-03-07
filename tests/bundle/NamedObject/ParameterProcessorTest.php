<?php

declare(strict_types=1);

namespace Netgen\Bundle\IbexaSiteApiBundle\Tests\NamedObject;

use DateTimeImmutable;
use Ibexa\Contracts\Core\Repository\PermissionResolver;
use Ibexa\Contracts\Core\Repository\Values\User\UserReference;
use Ibexa\Contracts\Core\SiteAccess\ConfigResolverInterface;
use Ibexa\Core\MVC\Symfony\ExpressionLanguage\ExpressionLanguage;
use Netgen\Bundle\IbexaSiteApiBundle\NamedObject\ExpressionFunctionProvider;
use Netgen\Bundle\IbexaSiteApiBundle\NamedObject\ParameterProcessor;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class ParameterProcessorTest extends TestCase
{
    public function providerForTestProcess(): array
    {
        $date = new DateTimeImmutable('@1');

        return [
            [
                null,
                null,
            ],
            [
                true,
                true,
            ],
            [
                false,
                false,
            ],
            [
                'string',
                'string',
            ],
            [
                24,
                24,
            ],
            [
                42.24,
                42.24,
            ],
            [
                [123],
                [123],
            ],
            [
                $date,
                $date,
            ],
            [
                "@=configResolver.getParameter('one', 'namespace', 'scope')",
                1,
            ],
            [
                "@=configResolver.getParameter('two', 'namespace', 'scope')",
                2,
            ],
            [
                "@=configResolver.getParameter('four')",
                4,
            ],
            [
                "@=configResolver.hasParameter('one', 'namespace', 'scope')",
                true,
            ],
            [
                "@=configResolver.hasParameter('two', 'namespace', 'scope')",
                true,
            ],
            [
                "@=configResolver.hasParameter('three', 'namespace', 'scope')",
                false,
            ],
            [
                "@=config('one', 'namespace', 'scope')",
                1,
            ],
            [
                "@=config('two', 'namespace', 'scope')",
                2,
            ],
            [
                "@=config('four')",
                4,
            ],
            [
                "@=currentUserId",
                123,
            ],
        ];
    }

    /**
     * @dataProvider providerForTestProcess
     *
     * @param mixed $parameter
     * @param mixed $expectedProcessedParameter
     */
    public function testProcess($parameter, $expectedProcessedParameter): void
    {
        $parameterProcessor = $this->getParameterProcessorUnderTest();

        $processedParameter = $parameterProcessor->process($parameter);

        self::assertSame($expectedProcessedParameter, $processedParameter);
    }

    public function testProcessLanguageExpressionValues(): void
    {
        $parameterProcessor = $this->getParameterProcessorUnderTest();

        self::assertInstanceOf(
            ConfigResolverInterface::class,
            $parameterProcessor->process('@=configResolver')
        );
    }

    protected function getParameterProcessorUnderTest(): ParameterProcessor
    {
        $configResolver = $this->getConfigResolverMock();
        $expressionLanguage = new ExpressionLanguage(null, [new ExpressionFunctionProvider()]);
        $permissionResolver = $this->getMockBuilder(PermissionResolver::class)->getMock();
        $userReference =  $this->getMockBuilder(UserReference::class)->getMock();

        $userReference
            ->method('getUserId')
            ->willReturn(123);

        $permissionResolver
            ->method('getCurrentUserReference')
            ->willReturn($userReference);

        return new ParameterProcessor(
            $expressionLanguage,
            $configResolver,
            $permissionResolver
        );
    }

    /**
     * @return \Ibexa\Contracts\Core\SiteAccess\ConfigResolverInterface
     */
    protected function getConfigResolverMock(): ConfigResolverInterface
    {
        $configResolverMock = $this->getMockBuilder(ConfigResolverInterface::class)->getMock();

        $getParameterMap = [
            ['one', 'namespace', 'scope', 1],
            ['two', 'namespace', 'scope', 2],
            ['four', null, null, 4],
        ];

        $configResolverMock
            ->method('getParameter')
            ->willReturnMap($getParameterMap);

        $hasParameterMap = [
            ['one', 'namespace', 'scope', true],
            ['two', 'namespace', 'scope', true],
            ['three', 'namespace', 'scope', false],
            ['four', null, null, true],
        ];

        $configResolverMock
            ->method('hasParameter')
            ->willReturnMap($hasParameterMap);

        return $configResolverMock;
    }
}
