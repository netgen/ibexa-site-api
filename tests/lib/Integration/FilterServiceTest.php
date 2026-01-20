<?php

declare(strict_types=1);

namespace Netgen\IbexaSiteApi\Tests\Integration;

use Ibexa\Contracts\Core\Repository\Values\Content\LocationQuery;
use Ibexa\Contracts\Core\Repository\Values\Content\Query;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion\ContentId;
use Ibexa\Contracts\Core\Repository\Values\Content\Search\SearchResult;
use PHPUnit\Framework\Attributes\Group;

/**
 * Test case for the FilterService.
 *
 * @see \Netgen\IbexaSiteApi\API\FilterService
 *
 * @depends Netgen\IbexaSiteApi\Tests\Integration\PrepareFixturesTest::testPrepareTestFixtures
 * @depends Netgen\IbexaSiteApi\Tests\Integration\SiteTest::testGetFilterService
 *
 * @internal
 */
#[Group('integration')]
#[Group('filter')]
final class FilterServiceTest extends BaseTest
{
    /**
     * Test for the findContent() method.
     *
     * @see \Netgen\IbexaSiteApi\API\FindService::findContent()
     */
    public function testFilterContentMatchPrimaryLanguage(): void
    {
        $this->overrideSettings(
            'prioritizedLanguages',
            [
                'eng-GB',
                'ger-DE',
            ],
        );

        $filterService = $this->getSite()->getFilterService();

        $data = $this->getData('eng-GB');
        $searchResult = $filterService->filterContent(
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
    public function testFilterContentMatchSecondaryLanguage(): void
    {
        $this->overrideSettings(
            'prioritizedLanguages',
            [
                'eng-US',
                'ger-DE',
            ],
        );

        $filterService = $this->getSite()->getFilterService();

        $data = $this->getData('ger-DE');
        $searchResult = $filterService->filterContent(
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
    public function testFilterContentMatchAlwaysAvailableLanguage(): void
    {
        $this->overrideSettings(
            'prioritizedLanguages',
            [
                'eng-US',
            ],
        );

        $filterService = $this->getSite()->getFilterService();

        $data = $this->getData('eng-GB');
        $searchResult = $filterService->filterContent(
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
    public function testFilterContentTranslationNotMatched(): void
    {
        $this->overrideSettings(
            'prioritizedLanguages',
            [
                'eng-GB',
                'ger-DE',
            ],
        );

        $filterService = $this->getSite()->getFilterService();

        $searchResult = $filterService->filterContent(
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
    public function testFilterLocationsMatchPrimaryLanguage(): void
    {
        $this->overrideSettings(
            'prioritizedLanguages',
            [
                'eng-GB',
                'ger-DE',
            ],
        );

        $filterService = $this->getSite()->getFilterService();

        $data = $this->getData('eng-GB');
        $searchResult = $filterService->filterLocations(
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
    public function testFilterLocationsMatchSecondaryLanguage(): void
    {
        $this->overrideSettings(
            'prioritizedLanguages',
            [
                'eng-US',
                'ger-DE',
            ],
        );

        $filterService = $this->getSite()->getFilterService();

        $data = $this->getData('ger-DE');
        $searchResult = $filterService->filterLocations(
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
    public function testFilterLocationsMatchAlwaysAvailableLanguage(): void
    {
        $this->overrideSettings(
            'prioritizedLanguages',
            [
                'eng-US',
            ],
        );

        $filterService = $this->getSite()->getFilterService();

        $data = $this->getData('eng-GB');
        $searchResult = $filterService->filterLocations(
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
    public function testFilterLocationsTranslationNotMatched(): void
    {
        $this->overrideSettings(
            'prioritizedLanguages',
            [
                'eng-GB',
                'ger-DE',
            ],
        );

        $filterService = $this->getSite()->getFilterService();

        $searchResult = $filterService->filterLocations(
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

        /** @var \Netgen\IbexaSiteApi\API\Values\Content $content */
        $content = $searchResult->searchHits[0]->valueObject;
        $this->assertContent($content, $data);
    }

    protected function assertLocationSearchResult(SearchResult $searchResult, $data): void
    {
        $languageCode = $data['languageCode'];

        self::assertSame(1, $searchResult->totalCount);
        self::assertSame($languageCode, $searchResult->searchHits[0]->matchedTranslation);

        /** @var \Netgen\IbexaSiteApi\API\Values\Location $location */
        $location = $searchResult->searchHits[0]->valueObject;
        $this->assertLocation($location, $data);
    }
}
