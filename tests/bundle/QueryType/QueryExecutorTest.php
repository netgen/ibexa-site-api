<?php

declare(strict_types=1);

namespace Netgen\Bundle\IbexaSiteApiBundle\Tests\QueryType;

use Ibexa\Contracts\Core\Repository\Repository;
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
use Netgen\IbexaSiteApi\Core\Site\Pagination\Pagerfanta\SudoFilterAdapter;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class QueryExecutorTest extends TestCase
{
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

    public function testSudoExecuteRaw(): void
    {
        $executor = $this->getQueryExecutorUnderTest();
        $result = $executor->sudoExecuteRaw(
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

    public function testSudoExecute(): void
    {
        $executor = $this->getQueryExecutorUnderTest();
        $result = $executor->sudoExecute(
            new QueryDefinition([
                'name' => 'content_query_type',
                'parameters' => ['parameters'],
                'useFilter' => true,
                'maxPerPage' => 20,
                'page' => 2,
            ]),
        );

        self::assertInstanceOf(SudoFilterAdapter::class, $result->getAdapter());
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
            $this->getRepositoryMock(),
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

    protected function getFilterServiceMock(): FilterService
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

    protected function getFindServiceMock(): FindService
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

    protected function getQueryTypeRegistryMock(): QueryTypeRegistry
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

    protected function getContentQueryTypeMock(): QueryType
    {
        $mock = $this->getMockBuilder(QueryType::class)->getMock();
        $mock
            ->method('getQuery')
            ->willReturn(new Query());

        return $mock;
    }

    protected function getLocationQueryTypeMock(): QueryType
    {
        $mock = $this->getMockBuilder(QueryType::class)->getMock();
        $mock
            ->method('getQuery')
            ->willReturn(new LocationQuery());

        return $mock;
    }

    protected function getRepositoryMock(): Repository
    {
        $repositoryMock = $this->getMockBuilder(Repository::class)->getMock();
        $repositoryMock
            ->method('sudo')
            ->with(self::anything())
            ->willReturnCallback(
                static fn (callable $callback) => $callback($repositoryMock),
            );

        return $repositoryMock;
    }
}
