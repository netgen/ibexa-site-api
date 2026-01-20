<?php

declare(strict_types=1);

namespace Netgen\IbexaSiteApi\Tests\Unit\Pagination\Pagerfanta;

use Ibexa\Contracts\Core\Repository\Values\Content\LocationQuery;
use Ibexa\Contracts\Core\Repository\Values\Content\Query;
use Ibexa\Contracts\Core\Repository\Values\Content\Search\AggregationResult\TermAggregationResult;
use Ibexa\Contracts\Core\Repository\Values\Content\Search\AggregationResultCollection;
use Ibexa\Contracts\Core\Repository\Values\Content\Search\SearchHit;
use Ibexa\Contracts\Core\Repository\Values\Content\Search\SearchResult;
use Netgen\IbexaSiteApi\API\FilterService;
use Netgen\IbexaSiteApi\Core\Site\Pagination\Pagerfanta\FilterAdapter;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[Group('pager')]
final class FilterAdapterTest extends TestCase
{
    protected FilterService|MockObject $filterService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->filterService = $this->getMockBuilder(FilterService::class)
            ->disableOriginalConstructor()
            ->onlyMethods([])
            ->getMock();
    }

    public function testGetNbResults(): void
    {
        $nbResults = 123;
        $query = new Query(['limit' => 10]);
        $countQuery = clone $query;
        $countQuery->limit = 0;
        $searchResult = new SearchResult(['totalCount' => $nbResults]);

        $this->filterService
            ->expects($this->once())
            ->method('filterContent')
            ->with(self::equalTo($countQuery))
            ->willReturn($searchResult);

        $adapter = $this->getAdapter($query);

        self::assertSame($nbResults, $adapter->getNbResults());
        self::assertSame($nbResults, $adapter->getNbResults());
    }

    public function testGetAggregations(): void
    {
        $aggregations = new AggregationResultCollection([
            new TermAggregationResult('aggregation'),
            new TermAggregationResult('aggregation'),
        ]);
        $query = new Query(['limit' => 10]);
        $countQuery = clone $query;
        $countQuery->limit = 0;
        $searchResult = new SearchResult(['aggregations' => $aggregations, 'totalCount' => 123]);

        $this->filterService
            ->expects($this->once())
            ->method('filterContent')
            ->with(self::equalTo($countQuery))
            ->willReturn($searchResult);

        $adapter = $this->getAdapter($query);

        self::assertSame($aggregations, $adapter->getAggregations());
        self::assertSame($aggregations, $adapter->getAggregations());
    }

    public function testMaxScore(): void
    {
        $maxScore = 100.0;
        $query = new Query(['limit' => 10]);
        $countQuery = clone $query;
        $countQuery->limit = 0;
        $searchResult = new SearchResult(['maxScore' => $maxScore, 'totalCount' => 123]);

        $this->filterService
            ->expects($this->once())
            ->method('filterContent')
            ->with(self::equalTo($countQuery))
            ->willReturn($searchResult);

        $adapter = $this->getAdapter($query);

        self::assertSame($maxScore, $adapter->getMaxScore());
        self::assertSame($maxScore, $adapter->getMaxScore());
    }

    public function testTimeIsNotSet(): void
    {
        $this->filterService
            ->expects($this->never())
            ->method('filterContent');

        $adapter = $this->getAdapter(new Query());

        self::assertNull($adapter->getTime());
        self::assertNull($adapter->getTime());
    }

    public function testGetSlice(): void
    {
        $offset = 20;
        $limit = 25;
        $nbResults = 123;
        $aggregations = new AggregationResultCollection([
            new TermAggregationResult('aggregation'),
            new TermAggregationResult('aggregation'),
        ]);
        $maxScore = 100.0;
        $time = 256.0;
        $query = new Query(['offset' => 5, 'limit' => 10]);
        $searchQuery = clone $query;
        $searchQuery->offset = $offset;
        $searchQuery->limit = $limit;
        $searchQuery->performCount = false;

        $hits = [new SearchHit(['valueObject' => 'Content'])];
        $searchResult = new SearchResult([
            'searchHits' => $hits,
            'totalCount' => $nbResults,
            'aggregations' => $aggregations,
            'maxScore' => $maxScore,
            'time' => $time,
        ]);

        $this->filterService
            ->expects($this->once())
            ->method('filterContent')
            ->with(self::equalTo($searchQuery))
            ->willReturn($searchResult);

        $adapter = $this->getAdapter($query);
        $slice = $adapter->getSlice($offset, $limit);

        self::assertSame($hits, $slice->getSearchHits());
        self::assertSame($nbResults, $adapter->getNbResults());
        self::assertSame($aggregations, $adapter->getAggregations());
        self::assertSame($maxScore, $adapter->getMaxScore());
        self::assertSame($time, (float) $adapter->getTime());
    }

    public function testLocationQuery(): void
    {
        $query = new LocationQuery(['performCount' => false]);

        $this->filterService
            ->expects($this->once())
            ->method('filterLocations')
            ->with(self::equalTo($query))
            ->willReturn(new SearchResult(['totalCount' => 123]));

        $adapter = $this->getAdapter($query);
        $adapter->getSlice(0, 25);
    }

    protected function getAdapter(Query $query): FilterAdapter
    {
        return new FilterAdapter($query, $this->filterService);
    }
}
