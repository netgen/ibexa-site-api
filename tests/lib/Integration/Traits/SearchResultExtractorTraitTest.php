<?php

declare(strict_types=1);

namespace Netgen\IbexaSiteApi\Tests\Integration\Traits;

use Ibexa\Contracts\Core\Repository\Values\Content\LocationQuery;
use Ibexa\Contracts\Core\Repository\Values\Content\Query;
use Netgen\IbexaSiteApi\API\Values\Content;
use Netgen\IbexaSiteApi\API\Values\Location;
use Netgen\IbexaSiteApi\Tests\Integration\BaseTest;

/**
 * @internal
 */
final class SearchResultExtractorTraitTest extends BaseTest
{
    /**
     * @var \Netgen\IbexaSiteApi\Tests\Integration\Traits\SearchResultExtractorStub
     */
    protected $stub;

    protected function setUp(): void
    {
        parent::setUp();
        $this->stub = new SearchResultExtractorStub();
    }

    public function testItExtractsValuesFromLocationSearchResult(): void
    {
        $locationIds = [5, 56];
        $findService = $this->getSite()->getFindService();
        $query = new LocationQuery(
            [
                'filter' => new Query\Criterion\LocationId($locationIds),
            ],
        );

        $searchResult = $findService->findLocations($query);

        $locationValueObjects = $this->stub->doExtractValueObjects($searchResult);

        foreach ($locationValueObjects as $value) {
            self::assertInstanceOf(Location::class, $value);
        }
    }

    public function testItExtractsValuesFromEmptyLocationSearchResult(): void
    {
        $locationIds = [54];
        $findService = $this->getSite()->getFindService();
        $query = new LocationQuery(
            [
                'filter' => new Query\Criterion\LocationId($locationIds),
            ],
        );

        $searchResult = $findService->findLocations($query);

        $locationValueObjects = $this->stub->doExtractValueObjects($searchResult);

        self::assertIsArray($locationValueObjects);
        self::assertEmpty($locationValueObjects);
    }

    public function testItExtractsValuesFromContentSearchResult(): void
    {
        $contentIds = [4, 54];
        $findService = $this->getSite()->getFindService();
        $query = new Query(
            [
                'filter' => new Query\Criterion\ContentId($contentIds),
            ],
        );

        $searchResult = $findService->findContent($query);

        $contentValueObjects = $this->stub->doExtractValueObjects($searchResult);

        foreach ($contentValueObjects as $value) {
            self::assertInstanceOf(Content::class, $value);
        }
    }

    public function testItExtractsValuesFromEmptyContentSearchResult(): void
    {
        $contentIds = [52];
        $findService = $this->getSite()->getFindService();
        $query = new Query(
            [
                'filter' => new Query\Criterion\ContentId($contentIds),
            ],
        );

        $searchResult = $findService->findContent($query);

        $contentValueObjects = $this->stub->doExtractValueObjects($searchResult);

        self::assertIsArray($contentValueObjects);
        self::assertEmpty($contentValueObjects);
    }

    public function testItExtractsContentItemsFromContentSearchResult(): void
    {
        $contentIds = [4, 41];
        $findService = $this->getSite()->getFindService();
        $query = new Query(
            [
                'filter' => new Query\Criterion\ContentId($contentIds),
            ],
        );

        $searchResult = $findService->findContent($query);

        $contentValueObjects = $this->stub->doExtractContentItems($searchResult);

        self::assertCount(2, $contentValueObjects);

        foreach ($contentValueObjects as $value) {
            self::assertInstanceOf(Content::class, $value);
        }
    }

    public function testItExtractsContentItemsFromEmptyContentSearchResult(): void
    {
        $contentIds = [52];
        $findService = $this->getSite()->getFindService();
        $query = new Query(
            [
                'filter' => new Query\Criterion\ContentId($contentIds),
            ],
        );

        $searchResult = $findService->findContent($query);

        $contentValueObjects = $this->stub->doExtractContentItems($searchResult);

        self::assertIsArray($contentValueObjects);
        self::assertEmpty($contentValueObjects);
    }

    public function testItExtractsLocationsFromLocationSearchResult(): void
    {
        $locationIds = [5, 12];
        $findService = $this->getSite()->getFindService();
        $query = new LocationQuery(
            [
                'filter' => new Query\Criterion\LocationId($locationIds),
            ],
        );

        $searchResult = $findService->findLocations($query);

        $locationValueObjects = $this->stub->doExtractLocations($searchResult);

        self::assertCount(2, $locationValueObjects);

        foreach ($locationValueObjects as $value) {
            self::assertInstanceOf(Location::class, $value);
        }
    }

    public function testItExtractsLocationsFromEmptyLocationSearchResult(): void
    {
        $locationIds = [54];
        $findService = $this->getSite()->getFindService();
        $query = new LocationQuery(
            [
                'filter' => new Query\Criterion\LocationId($locationIds),
            ],
        );

        $searchResult = $findService->findLocations($query);

        $locationValueObjects = $this->stub->doExtractLocations($searchResult);

        self::assertIsArray($locationValueObjects);
        self::assertEmpty($locationValueObjects);
    }
}
