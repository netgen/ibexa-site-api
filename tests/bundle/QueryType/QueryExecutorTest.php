<?php

declare(strict_types=1);

namespace Netgen\Bundle\IbexaSiteApiBundle\Tests\QueryType;

use Ibexa\Contracts\Core\Repository\Values\Content\LocationQuery;
use Ibexa\Contracts\Core\Repository\Values\Content\Query;
use Ibexa\Contracts\Core\Repository\Values\Content\Search\SearchResult;
use Ibexa\Core\QueryType\QueryType;
use Ibexa\Core\QueryType\QueryTypeRegistry;
use Netgen\Bundle\IbexaSiteApiBundle\QueryType\QueryDefinition;
use Netgen\Bundle\IbexaSiteApiBundle\QueryType\QueryExecutor;
use Netgen\IbexaSiteApi\API\FilterService;
use Netgen\IbexaSiteApi\API\FindService;
use Netgen\IbexaSiteApi\Core\Site\Pagination\Pagerfanta\FilterAdapter;
use Netgen\IbexaSiteApi\Core\Site\Pagination\Pagerfanta\FindAdapter;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class QueryExecutorTest extends TestCase
{
    /**
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException
     */
    public function testExecuteContentFilterQuery(): void
    {
        $executor = $this->getQueryExecutorUnderTest();
        $result = $executor->executeRaw(
            new QueryDefinition([
                'name' => 'content_query_type',
                'parameters' => ['parameters'],
                'useFilter' => true,
                'maxPerPage' => 20,
                'page' => 2,
            ]),
        );

        self::assertEquals($this->getFilterContentResult(), $result);
    }

    /**
     * @throws \Pagerfanta\Exception\Exception
     */
    public function testExecuteContentPagedFilterQuery(): void
    {
        $executor = $this->getQueryExecutorUnderTest();
        $result = $executor->execute(
            new QueryDefinition([
                'name' => 'content_query_type',
                'parameters' => ['parameters'],
                'useFilter' => true,
                'maxPerPage' => 20,
                'page' => 2,
            ]),
        );

        self::assertInstanceOf(FilterAdapter::class, $result->getAdapter());
        self::assertSame(20, $result->getMaxPerPage());
        self::assertSame(2, $result->getCurrentPage());
        self::assertTrue($result->getNormalizeOutOfRangePages());
    }

    /**
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException
     */
    public function testExecuteContentFindQuery(): void
    {
        $executor = $this->getQueryExecutorUnderTest();
        $result = $executor->executeRaw(
            new QueryDefinition([
                'name' => 'content_query_type',
                'parameters' => ['parameters'],
                'useFilter' => false,
                'maxPerPage' => 20,
                'page' => 2,
            ]),
        );

        self::assertEquals($this->getFindContentResult(), $result);
    }

    /**
     * @throws \Pagerfanta\Exception\Exception
     */
    public function testExecuteContentPagedFindQuery(): void
    {
        $executor = $this->getQueryExecutorUnderTest();
        $result = $executor->execute(
            new QueryDefinition([
                'name' => 'content_query_type',
                'parameters' => ['parameters'],
                'useFilter' => false,
                'maxPerPage' => 20,
                'page' => 2,
            ]),
        );

        self::assertInstanceOf(FindAdapter::class, $result->getAdapter());
        self::assertSame(20, $result->getMaxPerPage());
        self::assertSame(2, $result->getCurrentPage());
        self::assertTrue($result->getNormalizeOutOfRangePages());
    }

    /**
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException
     */
    public function testExecuteLocationFilterQuery(): void
    {
        $executor = $this->getQueryExecutorUnderTest();
        $result = $executor->executeRaw(
            new QueryDefinition([
                'name' => 'location_query_type',
                'parameters' => ['parameters'],
                'useFilter' => true,
                'maxPerPage' => 20,
                'page' => 2,
            ]),
        );

        self::assertEquals($this->getFilterLocationsResult(), $result);
    }

    /**
     * @throws \Pagerfanta\Exception\Exception
     */
    public function testExecuteLocationPagedFilterQuery(): void
    {
        $executor = $this->getQueryExecutorUnderTest();
        $result = $executor->execute(
            new QueryDefinition([
                'name' => 'location_query_type',
                'parameters' => ['parameters'],
                'useFilter' => true,
                'maxPerPage' => 20,
                'page' => 2,
            ]),
        );

        self::assertInstanceOf(FilterAdapter::class, $result->getAdapter());
        self::assertSame(20, $result->getMaxPerPage());
        self::assertSame(2, $result->getCurrentPage());
        self::assertTrue($result->getNormalizeOutOfRangePages());
    }

    /**
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException
     */
    public function testExecuteLocationFindQuery(): void
    {
        $executor = $this->getQueryExecutorUnderTest();
        $result = $executor->executeRaw(
            new QueryDefinition([
                'name' => 'location_query_type',
                'parameters' => ['parameters'],
                'useFilter' => false,
                'maxPerPage' => 20,
                'page' => 2,
            ]),
        );

        self::assertEquals($this->getFindLocationsResult(), $result);
    }

    /**
     * @throws \Pagerfanta\Exception\Exception
     */
    public function testExecuteLocationPagedFindQuery(): void
    {
        $executor = $this->getQueryExecutorUnderTest();
        $result = $executor->execute(
            new QueryDefinition([
                'name' => 'location_query_type',
                'parameters' => ['parameters'],
                'useFilter' => false,
                'maxPerPage' => 20,
                'page' => 2,
            ]),
        );

        self::assertInstanceOf(FindAdapter::class, $result->getAdapter());
        self::assertSame(20, $result->getMaxPerPage());
        self::assertSame(2, $result->getCurrentPage());
        self::assertTrue($result->getNormalizeOutOfRangePages());
    }

    protected function getQueryExecutorUnderTest(): QueryExecutor
    {
        return new QueryExecutor(
            $this->getQueryTypeRegistryMock(),
            $this->getFilterServiceMock(),
            $this->getFindServiceMock(),
        );
    }

    protected function getFilterContentResult(): SearchResult
    {
        return new SearchResult([
            'totalCount' => 100,
            'searchHits' => ['FILTER CONTENT'],
        ]);
    }

    protected function getFindContentResult(): SearchResult
    {
        return new SearchResult([
            'totalCount' => 100,
            'searchHits' => ['FIND CONTENT'],
        ]);
    }

    protected function getFilterLocationsResult(): SearchResult
    {
        return new SearchResult([
            'totalCount' => 100,
            'searchHits' => ['FILTER LOCATIONS'],
        ]);
    }

    protected function getFindLocationsResult(): SearchResult
    {
        return new SearchResult([
            'totalCount' => 100,
            'searchHits' => ['FIND LOCATIONS'],
        ]);
    }

    /**
     * @return \Netgen\IbexaSiteApi\API\FilterService|\PHPUnit\Framework\MockObject\MockObject
     */
    protected function getFilterServiceMock(): MockObject
    {
        $filterServiceMock = $this->getMockBuilder(FilterService::class)->getMock();
        $filterServiceMock
            ->method('filterContent')
            ->willReturn($this->getFilterContentResult());
        $filterServiceMock
            ->method('filterLocations')
            ->willReturn($this->getFilterLocationsResult());

        return $filterServiceMock;
    }

    /**
     * @return \Netgen\IbexaSiteApi\API\FindService|\PHPUnit\Framework\MockObject\MockObject
     */
    protected function getFindServiceMock(): MockObject
    {
        $findServiceMock = $this->getMockBuilder(FindService::class)->getMock();
        $findServiceMock
            ->method('findContent')
            ->willReturn($this->getFindContentResult());
        $findServiceMock
            ->method('findLocations')
            ->willReturn($this->getFindLocationsResult());

        return $findServiceMock;
    }

    /**
     * @return \Ibexa\Core\QueryType\QueryTypeRegistry|\PHPUnit\Framework\MockObject\MockObject
     */
    protected function getQueryTypeRegistryMock(): MockObject
    {
        $queryTypeRegistryMock = $this->getMockBuilder(QueryTypeRegistry::class)->getMock();

        $queryTypeRegistryMock
            ->method('getQueryType')
            ->willReturnMap([
                ['content_query_type', $this->getContentQueryTypeMock()],
                ['location_query_type', $this->getLocationQueryTypeMock()],
            ]);

        return $queryTypeRegistryMock;
    }

    protected function getContentQueryTypeMock(): MockObject
    {
        $mock = $this->getMockBuilder(QueryType::class)->getMock();
        $mock
            ->method('getQuery')
            ->willReturn(new Query());

        return $mock;
    }

    protected function getLocationQueryTypeMock(): MockObject
    {
        $mock = $this->getMockBuilder(QueryType::class)->getMock();
        $mock
            ->method('getQuery')
            ->willReturn(new LocationQuery());

        return $mock;
    }
}
