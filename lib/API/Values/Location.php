<?php

declare(strict_types=1);

namespace Netgen\IbexaSiteApi\API\Values;

use Ibexa\Contracts\Core\Repository\Values\ValueObject;
use Pagerfanta\Pagerfanta;

/**
 * Site Location represents location of Site Content object in the content tree.
 *
 * Corresponds to Ibexa Repository Location object.
 *
 * @see \Ibexa\Contracts\Core\Repository\Values\Content\Location
 *
 * @property-read int $id
 * @property-read int $status
 * @property-read int $priority
 * @property-read bool $hidden
 * @property-read bool $invisible
 * @property-read bool $explicitlyHidden
 * @property-read bool $isVisible
 * @property-read string $remoteId
 * @property-read int $parentLocationId
 * @property-read string $pathString
 * @property-read int[] $path
 * @property-read int $depth
 * @property-read int $sortField
 * @property-read int $sortOrder
 * @property-read int $contentId
 * @property-read \Netgen\IbexaSiteApi\API\Values\Content $content
 * @property-read \Netgen\IbexaSiteApi\API\Values\ContentInfo $contentInfo
 * @property-read ?\Netgen\IbexaSiteApi\API\Values\Location $parent
 * @property-read \Ibexa\Contracts\Core\Repository\Values\Content\Location $innerLocation
 */
abstract class Location extends ValueObject
{
    /**
     * Return an array of children Locations, limited by optional $limit.
     *
     * @return \Netgen\IbexaSiteApi\API\Values\Location[]
     */
    abstract public function getChildren(int $limit = 25): array;

    /**
     * Return an array of children Locations, filtered by optional
     * $contentTypeIdentifiers, $maxPerPage and $currentPage.
     */
    abstract public function filterChildren(array $contentTypeIdentifiers = [], int $maxPerPage = 25, int $currentPage = 1): Pagerfanta;

    /**
     * Return first child, limited by optional $contentTypeIdentifier.
     */
    abstract public function getFirstChild(?string $contentTypeIdentifier = null): ?self;

    /**
     * Return an array of Location siblings, limited by optional $limit.
     *
     * @return \Netgen\IbexaSiteApi\API\Values\Location[]
     */
    abstract public function getSiblings(int $limit = 25): array;

    /**
     * Return an array of Location siblings, filtered by optional
     * $contentTypeIdentifiers, $maxPerPage and $currentPage.
     *
     * Siblings will not include current Location.
     */
    abstract public function filterSiblings(array $contentTypeIdentifiers = [], int $maxPerPage = 25, int $currentPage = 1): Pagerfanta;

    /**
     * Get SortClause objects built from Locations' sort options.
     *
     * In difference to the sort clauses returned by the Repository Location,
     * ContentName sort clause from Search Extra will be used, working on the
     * translated Content name with both Legacy and Solr search engines.
     *
     * @see \Netgen\IbexaSearchExtra\API\Values\Content\Query\SortClause\ContentName
     * @see \Ibexa\Contracts\Core\Repository\Values\Content\Location::getSortClauses()
     *
     * @return \Ibexa\Contracts\Core\Repository\Values\Content\Query\SortClause[]
     */
    abstract public function getSortClauses(): array;
}
