<?php

declare(strict_types=1);

namespace Netgen\IbexaSiteApi\Tests\Integration;

use Ibexa\Contracts\Core\Repository\Values\Content\LocationQuery;
use Ibexa\Contracts\Core\Repository\Values\Content\Query;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion\ContentId;
use Ibexa\Contracts\Core\Repository\Values\Content\Search\SearchResult;
use Netgen\IbexaSiteApi\API\Values\Content;
use Netgen\IbexaSiteApi\API\Values\Location;
use PHPUnit\Framework\Attributes\Group;

/**
 * Test case for the FindService.
 *
 * @see \Netgen\IbexaSiteApi\API\FindService
 *
 * @depends Netgen\IbexaSiteApi\Tests\Integration\PrepareFixturesTest::testPrepareTestFixtures
 * @depends Netgen\IbexaSiteApi\Tests\Integration\SiteTest::testGetFindService
 *
 * @internal
 */
#[Group('integration')]
#[Group('find')]
final class FindServiceTest extends BaseTestCase
{
    /**
     * Test for the findContent() method.
     *
     * @see \Netgen\IbexaSiteApi\API\FindService::findContent()
     */
    public function testFindContentMatchPrimaryLanguage(): void
    {
        $this->overrideSettings(
            'prioritizedLanguages',
            [
                'eng-GB',
                'ger-DE',
            ],
        );

        $findService = $this->getSite()->getFindService();

        $data = $this->getData('eng-GB');
        $searchResult = $findService->findContent(
            new Query([
                'filter' => new ContentId($data['contentId']),
            ]),
        );

        $this->assertContentSearchResult($searchResult, $data);
    }

    /**
     * Test for the findContent() method.
     *
     * @see \Netgen\IbexaSiteApi\API\FindService::findContent()
     */
    public function testFindContentMatchSecondaryLanguage(): void
    {
        $this->overrideSettings(
            'prioritizedLanguages',
            [
                'eng-US',
                'ger-DE',
            ],
        );

        $findService = $this->getSite()->getFindService();

        $data = $this->getData('ger-DE');
        $searchResult = $findService->findContent(
            new Query([
                'filter' => new ContentId($data['contentId']),
            ]),
        );

        $this->assertContentSearchResult($searchResult, $data);
    }

    /**
     * Test for the findContent() method.
     *
     * @see \Netgen\IbexaSiteApi\API\FindService::findContent()
     */
    public function testFindContentMatchAlwaysAvailableLanguage(): void
    {
        $this->overrideSettings(
            'prioritizedLanguages',
            [
                'eng-US',
            ],
        );

        $findService = $this->getSite()->getFindService();

        $data = $this->getData('eng-GB');
        $searchResult = $findService->findContent(
            new Query([
                'filter' => new ContentId($data['contentId']),
            ]),
        );

        $this->assertContentSearchResult($searchResult, $data);
    }

    /**
     * Test for the findContent() method.
     *
     * @see \Netgen\IbexaSiteApi\API\FindService::findContent()
     */
    public function testFindContentTranslationNotMatched(): void
    {
        $this->overrideSettings(
            'prioritizedLanguages',
            [
                'eng-GB',
                'ger-DE',
            ],
        );

        $findService = $this->getSite()->getFindService();

        $searchResult = $findService->findContent(
            new Query([
                'filter' => new ContentId(52),
            ]),
        );

        self::assertSame(0, $searchResult->totalCount);
    }

    /**
     * Test for the findLocations() method.
     *
     * @see \Netgen\IbexaSiteApi\API\FindService::findLocations()
     */
    public function testFindLocationsMatchPrimaryLanguage(): void
    {
        $this->overrideSettings(
            'prioritizedLanguages',
            [
                'eng-GB',
                'ger-DE',
            ],
        );

        $findService = $this->getSite()->getFindService();

        $data = $this->getData('eng-GB');
        $searchResult = $findService->findLocations(
            new LocationQuery([
                'filter' => new ContentId($data['contentId']),
            ]),
        );

        $this->assertLocationSearchResult($searchResult, $data);
    }

    /**
     * Test for the findLocations() method.
     *
     * @see \Netgen\IbexaSiteApi\API\FindService::findLocations()
     */
    public function testFindLocationsMatchSecondaryLanguage(): void
    {
        $this->overrideSettings(
            'prioritizedLanguages',
            [
                'eng-US',
                'ger-DE',
            ],
        );

        $findService = $this->getSite()->getFindService();

        $data = $this->getData('ger-DE');
        $searchResult = $findService->findLocations(
            new LocationQuery([
                'filter' => new ContentId($data['contentId']),
            ]),
        );

        $this->assertLocationSearchResult($searchResult, $data);
    }

    /**
     * Test for the findLocations() method.
     *
     * @see \Netgen\IbexaSiteApi\API\FindService::findLocations()
     */
    public function testFindLocationsMatchAlwaysAvailableLanguage(): void
    {
        $this->overrideSettings(
            'prioritizedLanguages',
            [
                'eng-US',
            ],
        );

        $findService = $this->getSite()->getFindService();

        $data = $this->getData('eng-GB');
        $searchResult = $findService->findLocations(
            new LocationQuery([
                'filter' => new ContentId($data['contentId']),
            ]),
        );

        $this->assertLocationSearchResult($searchResult, $data);
    }

    /**
     * Test for the findLocations() method.
     *
     * @see \Netgen\IbexaSiteApi\API\FindService::findLocations()
     */
    public function testFindLocationsTranslationNotMatched(): void
    {
        $this->overrideSettings(
            'prioritizedLanguages',
            [
                'eng-GB',
                'ger-DE',
            ],
        );

        $findService = $this->getSite()->getFindService();

        $searchResult = $findService->findLocations(
            new LocationQuery([
                'filter' => new ContentId(52),
            ]),
        );

        self::assertSame(0, $searchResult->totalCount);
    }

    protected function assertContentSearchResult(SearchResult $searchResult, $data): void
    {
        $languageCode = $data['languageCode'];

        self::assertSame(1, $searchResult->totalCount);
        self::assertSame($languageCode, $searchResult->searchHits[0]->matchedTranslation);
        $content = $searchResult->searchHits[0]->valueObject;
        self::assertInstanceOf(Content::class, $content);
        $this->assertContent($content, $data);
    }

    protected function assertLocationSearchResult(SearchResult $searchResult, $data): void
    {
        $languageCode = $data['languageCode'];

        self::assertSame(1, $searchResult->totalCount);
        self::assertSame($languageCode, $searchResult->searchHits[0]->matchedTranslation);
        $location = $searchResult->searchHits[0]->valueObject;
        self::assertInstanceOf(Location::class, $location);
        $this->assertLocation($location, $data);
    }
}
