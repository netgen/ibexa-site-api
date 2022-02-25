<?php

declare(strict_types=1);

namespace Netgen\IbexaSiteApi\Tests\Integration\Traits;

use Ibexa\Contracts\Core\Repository\Values\Content\Search\SearchResult;
use Netgen\IbexaSiteApi\Core\Traits\SearchResultExtractorTrait;

class SearchResultExtractorStub
{
    use SearchResultExtractorTrait;

    /**
     * @return \Ibexa\Contracts\Core\Repository\Values\ValueObject[]
     */
    public function doExtractValueObjects(SearchResult $searchResult): array
    {
        return $this->extractValueObjects($searchResult);
    }

    /**
     * @return \Ibexa\Contracts\Core\Repository\Values\ValueObject[]
     */
    public function doExtractContentItems(SearchResult $searchResult): array
    {
        return $this->extractContentItems($searchResult);
    }

    /**
     * @return \Ibexa\Contracts\Core\Repository\Values\ValueObject[]
     */
    public function doExtractLocations(SearchResult $searchResult): array
    {
        return $this->extractLocations($searchResult);
    }
}
