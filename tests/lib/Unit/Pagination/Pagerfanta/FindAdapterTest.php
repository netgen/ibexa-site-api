<?php

declare(strict_types=1);

namespace Netgen\IbexaSiteApi\Tests\Unit\Pagination\Pagerfanta;

use Ibexa\Contracts\Core\Repository\Values\Content\LocationQuery;
use Ibexa\Contracts\Core\Repository\Values\Content\Query;
use Ibexa\Contracts\Core\Repository\Values\Content\Search\SearchHit;
use Ibexa\Contracts\Core\Repository\Values\Content\Search\SearchResult;
use Netgen\IbexaSearchExtra\Core\Pagination\Pagerfanta\Slice;
use Netgen\IbexaSiteApi\API\FindService;
use Netgen\IbexaSiteApi\Core\Site\Pagination\Pagerfanta\FindAdapter;
use PHPUnit\Framework\TestCase;

/**
 * @group pager
 *
 * @internal
 */
final class FindAdapterTest extends TestCase
{
    /**
     * @var \Netgen\IbexaSiteApi\API\FindService|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $findService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->findService = $this->getMockBuilder(FindService::class)
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

        $this->findService
            ->expects(self::once())
            ->method('findContent')
            ->with(self::equalTo($countQuery))
            ->willReturn($searchResult);

        $adapter = $this->getAdapter($query);

        self::assertSame($nbResults, $adapter->getNbResults());
        self::assertSame($nbResults, $adapter->getNbResults());
    }

    public function testGetFacets(): void
    {
        $facets = ['facet', 'facet'];
        $query = new Query(['limit' => 10]);
        $countQuery = clone $query;
        $countQuery->limit = 0;
        $searchResult = new SearchResult(['facets' => $facets]);

        $this->findService
            ->expects(self::once())
            ->method('findContent')
            ->with(self::equalTo($countQuery))
            ->willReturn($searchResult);

        $adapter = $this->getAdapter($query);

        self::assertSame($facets, $adapter->getFacets());
        self::assertSame($facets, $adapter->getFacets());
    }

    public function testMaxScore(): void
    {
        $maxScore = 100.0;
        $query = new Query(['limit' => 10]);
        $countQuery = clone $query;
        $countQuery->limit = 0;
        $searchResult = new SearchResult(['maxScore' => $maxScore]);

        $this->findService
            ->expects(self::once())
            ->method('findContent')
            ->with(self::equalTo($countQuery))
            ->willReturn($searchResult);

        $adapter = $this->getAdapter($query);

        self::assertSame($maxScore, $adapter->getMaxScore());
        self::assertSame($maxScore, $adapter->getMaxScore());
    }

    public function testTimeIsNotSet(): void
    {
        $this->findService
            ->expects(self::never())
            ->method('findContent');

        $adapter = $this->getAdapter(new Query());

        self::assertNull($adapter->getTime());
        self::assertNull($adapter->getTime());
    }

    public function testGetSlice(): void
    {
        $offset = 20;
        $limit = 25;
        $nbResults = 123;
        $facets = ['facet', 'facet'];
        $maxScore = 100.0;
        $time = 256;
        $query = new Query(['offset' => 5, 'limit' => 10]);
        $searchQuery = clone $query;
        $searchQuery->offset = $offset;
        $searchQuery->limit = $limit;
        $searchQuery->performCount = false;

        $hits = [new SearchHit(['valueObject' => 'Content'])];
        $searchResult = new SearchResult([
            'searchHits' => $hits,
            'totalCount' => $nbResults,
            'facets' => $facets,
            'maxScore' => $maxScore,
            'time' => $time,
        ]);

        $this->findService
            ->expects(self::once())
            ->method('findContent')
            ->with(self::equalTo($searchQuery))
            ->willReturn($searchResult);

        $adapter = $this->getAdapter($query);
        $slice = $adapter->getSlice($offset, $limit);

        self::assertSame($hits, $slice->getSearchHits());
        self::assertSame($nbResults, $adapter->getNbResults());
        self::assertSame($facets, $adapter->getFacets());
        self::assertSame($maxScore, $adapter->getMaxScore());
        self::assertSame($time, $adapter->getTime());
    }

    public function testLocationQuery(): void
    {
        $query = new LocationQuery(['performCount' => false]);

        $this->findService
            ->expects(self::once())
            ->method('findLocations')
            ->with(self::equalTo($query))
            ->willReturn(new SearchResult());

        $adapter = $this->getAdapter($query);
        $adapter->getSlice(0, 25);
    }

    protected function getAdapter(Query $query): FindAdapter
    {
        return new FindAdapter($query, $this->findService);
    }
}
