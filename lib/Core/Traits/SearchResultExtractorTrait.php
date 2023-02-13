<?php

declare(strict_types=1);

namespace Netgen\IbexaSiteApi\Core\Traits;

use Ibexa\Contracts\Core\Repository\Values\Content\Search\SearchHit;
use Ibexa\Contracts\Core\Repository\Values\Content\Search\SearchResult;
use Netgen\IbexaSiteApi\API\Values\Content;
use Netgen\IbexaSiteApi\API\Values\Location;

use function array_map;

/**
 * SearchResultExtractorTrait provides a way to extract value objects
 * (usually Content items or Locations) for Ibexa CMS SearchResult.
 *
 * @see \Ibexa\Contracts\Core\Repository\Values\Content\Search\SearchResult
 */
trait SearchResultExtractorTrait
{
    /**
     * Extracts value objects from SearchResult.
     *
     * @return \Ibexa\Contracts\Core\Repository\Values\ValueObject[]
     */
    protected function extractValueObjects(SearchResult $searchResult): array
    {
        return array_map(
            static fn (SearchHit $searchHit) => $searchHit->valueObject,
            $searchResult->searchHits,
        );
    }

    /**
     * Extracts Content items from SearchResult.
     *
     * @return \Netgen\IbexaSiteApi\API\Values\Content[]
     */
    protected function extractContentItems(SearchResult $searchResult): array
    {
        return array_map(
            static function (SearchHit $searchHit): Content {
                /** @var \Netgen\IbexaSiteApi\API\Values\Content $content */
                $content = $searchHit->valueObject;

                return $content;
            },
            $searchResult->searchHits,
        );
    }

    /**
     * Extracts Locations from SearchResult.
     *
     * @return \Netgen\IbexaSiteApi\API\Values\Location[]
     */
    protected function extractLocations(SearchResult $searchResult): array
    {
        return array_map(
            static function (SearchHit $searchHit): Location {
                /** @var \Netgen\IbexaSiteApi\API\Values\Location $location */
                $location = $searchHit->valueObject;

                return $location;
            },
            $searchResult->searchHits,
        );
    }
}
