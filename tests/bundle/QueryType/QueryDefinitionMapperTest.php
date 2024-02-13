<?php

declare(strict_types=1);

namespace Netgen\Bundle\IbexaSiteApiBundle\Tests\QueryType;

use Ibexa\Contracts\Core\SiteAccess\ConfigResolverInterface;
use Ibexa\Core\MVC\Symfony\ExpressionLanguage\ExpressionLanguage;
use Ibexa\Core\QueryType\QueryType;
use Ibexa\Core\QueryType\QueryTypeRegistry;
use Netgen\Bundle\IbexaSiteApiBundle\NamedObject\Provider;
use Netgen\Bundle\IbexaSiteApiBundle\QueryType\ExpressionFunctionProvider;
use Netgen\Bundle\IbexaSiteApiBundle\QueryType\ParameterProcessor;
use Netgen\Bundle\IbexaSiteApiBundle\QueryType\QueryDefinition;
use Netgen\Bundle\IbexaSiteApiBundle\QueryType\QueryDefinitionMapper;
use Netgen\Bundle\IbexaSiteApiBundle\View\ContentView;
use Netgen\IbexaSiteApi\API\Values\Content;
use Netgen\IbexaSiteApi\API\Values\Location;
use Netgen\IbexaSiteApi\Core\Site\QueryType\QueryType as SiteQueryType;
use OutOfBoundsException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * @internal
 */
final class QueryDefinitionMapperTest extends TestCase
{
    public function provideMapCases(): iterable
    {
        $locationMock = $this->getMockBuilder(Location::class)->getMock();
        $contentMock = $this->getMockBuilder(Content::class)->getMock();

        return [
            [
                [
                    'query_type' => 'query_type',
                    'use_filter' => true,
                    'max_per_page' => 10,
                    'page' => 1,
                    'parameters' => [
                        'some' => 'parameters',
                    ],
                ],
                new QueryDefinition([
                    'name' => 'query_type',
                    'parameters' => [
                        'some' => 'parameters',
                    ],
                    'useFilter' => true,
                    'maxPerPage' => 10,
                    'page' => 1,
                ]),
            ],
            [
                [
                    'query_type' => 'site_query_type',
                    'use_filter' => true,
                    'max_per_page' => 10,
                    'page' => 1,
                    'parameters' => [
                        'some' => 'parameters',
                        'content' => $contentMock,
                        'location' => $locationMock,
                    ],
                ],
                new QueryDefinition([
                    'name' => 'site_query_type',
                    'parameters' => [
                        'some' => 'parameters',
                        'content' => $contentMock,
                        'location' => $locationMock,
                    ],
                    'useFilter' => true,
                    'maxPerPage' => 10,
                    'page' => 1,
                ]),
            ],
            [
                [
                    'query_type' => 'site_query_type',
                    'use_filter' => true,
                    'max_per_page' => 10,
                    'page' => 1,
                    'parameters' => [
                        'some' => 'parameters',
                    ],
                ],
                new QueryDefinition([
                    'name' => 'site_query_type',
                    'parameters' => [
                        'some' => 'parameters',
                        'content' => $contentMock,
                        'location' => $locationMock,
                    ],
                    'useFilter' => true,
                    'maxPerPage' => 10,
                    'page' => 1,
                ]),
            ],
            [
                [
                    'named_query' => 'named_query',
                    'page' => 2,
                    'parameters' => [
                        'some' => 'pancakes',
                    ],
                ],
                new QueryDefinition([
                    'name' => 'query_type',
                    'parameters' => [
                        'some' => 'pancakes',
                        'chair' => 'table',
                    ],
                    'useFilter' => true,
                    'maxPerPage' => 10,
                    'page' => 2,
                ]),
            ],
            [
                [
                    'named_query' => 'named_site_query',
                    'page' => 3,
                    'parameters' => [
                        'some' => [
                            'various' => 'delicacies',
                        ],
                        'salad' => true,
                    ],
                    'use_filter' => false,
                ],
                new QueryDefinition([
                    'name' => 'site_query_type',
                    'parameters' => [
                        'some' => [
                            'various' => 'delicacies',
                        ],
                        'salad' => true,
                        'content' => $contentMock,
                        'location' => $locationMock,
                        'spoon' => 'soup',
                    ],
                    'useFilter' => false,
                    'maxPerPage' => 10,
                    'page' => 3,
                ]),
            ],
        ];
    }

    /**
     * @dataProvider provideMapCases
     */
    public function testMap(array $configuration, QueryDefinition $expectedQueryDefinition): void
    {
        $queryDefinitionMapper = $this->getQueryDefinitionMapperUnderTest();

        $queryDefinition = $queryDefinitionMapper->map($configuration, $this->getViewMock());

        self::assertEquals($expectedQueryDefinition, $queryDefinition);
    }

    public function testMapNonexistentNamedQueryThrowsException(): void
    {
        $this->expectException(OutOfBoundsException::class);
        $this->expectExceptionMessage(
            "Could not find query configuration named 'bazooka'",
        );

        $queryDefinitionMapper = $this->getQueryDefinitionMapperUnderTest();

        $queryDefinitionMapper->map(
            [
                'named_query' => 'bazooka',
                'page' => 3,
                'parameters' => [
                    'some' => 'steaks',
                    'salad' => true,
                ],
                'use_filter' => false,
            ],
            $this->getViewMock(),
        );
    }

    protected function getQueryDefinitionMapperUnderTest(): QueryDefinitionMapper
    {
        return new QueryDefinitionMapper(
            $this->getQueryTypeRegistryMock(),
            $this->getParameterProcessor(),
            $this->getConfigResolverMock(),
        );
    }

    protected function getConfigResolverMock(): ConfigResolverInterface
    {
        $configResolverMock = $this->getMockBuilder(ConfigResolverInterface::class)->getMock();

        $configResolverMock
            ->method('getParameter')
            ->with('ng_site_api.named_queries')
            ->willReturn([
                'named_query' => [
                    'query_type' => 'query_type',
                    'use_filter' => true,
                    'max_per_page' => 10,
                    'page' => 1,
                    'parameters' => [
                        'some' => [
                            'parameters' => 'and stuff',
                        ],
                        'chair' => 'table',
                    ],
                ],
                'named_site_query' => [
                    'query_type' => 'site_query_type',
                    'use_filter' => true,
                    'max_per_page' => 10,
                    'page' => 1,
                    'parameters' => [
                        'some' => [
                            'parameters' => 'and other stuff',
                        ],
                        'spoon' => 'soup',
                    ],
                ],
            ]);

        return $configResolverMock;
    }

    protected function getQueryTypeRegistryMock(): QueryTypeRegistry
    {
        $queryTypeRegistryMock = $this->getMockBuilder(QueryTypeRegistry::class)->getMock();
        $queryTypeRegistryMock
            ->method('getQueryType')
            ->willReturnMap([
                ['query_type', $this->getQueryTypeMock()],
                ['site_query_type', $this->getSiteQueryTypeMock()],
            ]);

        return $queryTypeRegistryMock;
    }

    protected function getQueryTypeMock(): MockObject
    {
        return $this->getMockBuilder(QueryType::class)->getMock();
    }

    protected function getSiteQueryTypeMock(): MockObject
    {
        $queryTypeMock = $this->getMockBuilder(SiteQueryType::class)->getMock();
        $queryTypeMock
            ->method('supportsParameter')
            ->willReturnMap([
                ['content', true],
                ['location', true],
            ]);

        return $queryTypeMock;
    }

    protected function getParameterProcessor(): ParameterProcessor
    {
        /** @var \Symfony\Component\HttpFoundation\RequestStack $requestStack */
        $requestStack = $this->getMockBuilder(RequestStack::class)->getMock();

        /** @var \Ibexa\Contracts\Core\SiteAccess\ConfigResolverInterface $configResolverMock */
        $configResolverMock = $this->getMockBuilder(ConfigResolverInterface::class)->getMock();

        /** @var \Netgen\Bundle\IbexaSiteApiBundle\NamedObject\Provider $namedObjectProviderMock */
        $namedObjectProviderMock = $this->getMockBuilder(Provider::class)->getMock();
        $expressionLanguage = new ExpressionLanguage(null, [new ExpressionFunctionProvider()]);

        return new ParameterProcessor($expressionLanguage, $requestStack, $configResolverMock, $namedObjectProviderMock);
    }

    protected function getViewMock(): ContentView
    {
        $viewMock = $this->getMockBuilder(ContentView::class)->getMock();

        $locationMock = $this->getMockBuilder(Location::class)->getMock();
        $contentMock = $this->getMockBuilder(Content::class)->getMock();

        $viewMock->method('getSiteLocation')->willReturn($locationMock);
        $viewMock->method('getSiteContent')->willReturn($contentMock);

        return $viewMock;
    }
}
