<?php

declare(strict_types=1);

namespace Netgen\IbexaSiteApi\API\Values;

use Ibexa\Contracts\Core\FieldType\Value;
use Ibexa\Contracts\Core\Repository\Values\ValueObject;
use Pagerfanta\Pagerfanta;

/**
 * Site Content object represents Ibexa Repository Content object in a current version
 * and specific language.
 *
 * Corresponds to Ibexa Repository Content object.
 *
 * @see \Ibexa\Contracts\Core\Repository\Values\Content\Content
 *
 * @property-read int $id
 * @property-read ?int $mainLocationId
 * @property-read ?string $name
 * @property-read string $languageCode
 * @property-read bool $isVisible
 * @property-read \Netgen\IbexaSiteApi\API\Values\Path $path
 * @property-read \Netgen\IbexaSiteApi\API\Values\Url $url
 * @property-read \Netgen\IbexaSiteApi\API\Values\ContentInfo $contentInfo
 * @property-read \Netgen\IbexaSiteApi\API\Values\Field[]|\Netgen\IbexaSiteApi\API\Values\Fields $fields
 * @property-read ?\Netgen\IbexaSiteApi\API\Values\Location $mainLocation
 * @property-read ?\Netgen\IbexaSiteApi\API\Values\Content $owner
 * @property-read ?\Netgen\IbexaSiteApi\API\Values\Content $modifier
 * @property-read ?\Ibexa\Contracts\Core\Repository\Values\User\User $innerOwnerUser
 * @property-read ?\Ibexa\Contracts\Core\Repository\Values\User\User $innerModifierUser
 * @property-read \Ibexa\Contracts\Core\Repository\Values\Content\Content $innerContent
 * @property-read \Ibexa\Contracts\Core\Repository\Values\Content\VersionInfo $innerVersionInfo
 * @property-read \Ibexa\Contracts\Core\Repository\Values\Content\VersionInfo $versionInfo
 */
abstract class Content extends ValueObject
{
    /**
     * Returns if Content has the field with the given field definition $identifier.
     */
    abstract public function hasField(string $identifier): bool;

    /**
     * Return the Field object for the given field definition $identifier.
     */
    abstract public function getField(string $identifier): Field;

    /**
     * Returns if Content has the field with the given field $id.
     */
    abstract public function hasFieldById(int $id): bool;

    /**
     * Return Field object for the given field $id.
     */
    abstract public function getFieldById(int $id): Field;

    /**
     * Return the first existing and non-empty field.
     *
     * If no field is found in the Content, a surrogate field will be returned.
     * If all found fields are empty, the first found field will be returned.
     */
    abstract public function getFirstNonEmptyField(string $firstIdentifier, string ...$otherIdentifiers): Field;

    /**
     * Returns a field value for the given field definition identifier.
     */
    abstract public function getFieldValue(string $identifier): Value;

    /**
     * Returns a field value for the given field $id.
     */
    abstract public function getFieldValueById(int $id): Value;

    /**
     * Return an array of Locations, limited by optional $limit.
     *
     * @return \Netgen\IbexaSiteApi\API\Values\Location[]
     */
    abstract public function getLocations(int $limit = 25): array;

    /**
     * Return an array of Locations, limited by optional $maxPerPage and $currentPage.
     *
     * @return \Pagerfanta\Pagerfanta Pagerfanta instance iterating over Site API Locations
     */
    abstract public function filterLocations(int $maxPerPage = 25, int $currentPage = 1): Pagerfanta;

    /**
     * Return single related Content from $fieldDefinitionIdentifier field.
     */
    abstract public function getFieldRelation(string $fieldDefinitionIdentifier): ?self;

    /**
     * Return single related Content from $fieldDefinitionIdentifier field using repository sudo.
     */
    abstract public function getSudoFieldRelation(string $fieldDefinitionIdentifier): ?self;

    /**
     * Return all related Content from $fieldDefinitionIdentifier.
     *
     * @return \Netgen\IbexaSiteApi\API\Values\Content[]
     */
    abstract public function getFieldRelations(string $fieldDefinitionIdentifier, int $limit = 25): array;

    /**
     * Return all related Content from $fieldDefinitionIdentifier using repository sudo.
     *
     * @return \Netgen\IbexaSiteApi\API\Values\Content[]
     */
    abstract public function getSudoFieldRelations(string $fieldDefinitionIdentifier, int $limit = 25): array;

    /**
     * Return related Content from $fieldDefinitionIdentifier field,
     * optionally limited by a list of $contentTypeIdentifiers.
     *
     * @param string[] $contentTypeIdentifiers
     *
     * @return \Pagerfanta\Pagerfanta Pagerfanta instance iterating over Site API Content items
     */
    abstract public function filterFieldRelations(
        string $fieldDefinitionIdentifier,
        array $contentTypeIdentifiers = [],
        int $maxPerPage = 25,
        int $currentPage = 1,
    ): Pagerfanta;

    /**
     * Return related Content from $fieldDefinitionIdentifier field using repository sudo,
     * optionally limited by a list of $contentTypeIdentifiers.
     *
     * @param string[] $contentTypeIdentifiers
     *
     * @return \Pagerfanta\Pagerfanta Pagerfanta instance iterating over Site API Content items
     */
    abstract public function filterSudoFieldRelations(
        string $fieldDefinitionIdentifier,
        array $contentTypeIdentifiers = [],
        int $maxPerPage = 25,
        int $currentPage = 1,
    ): Pagerfanta;

    /**
     * Return single related Location from $fieldDefinitionIdentifier field.
     */
    abstract public function getFieldRelationLocation(string $fieldDefinitionIdentifier): ?Location;

    /**
     * Return single related Location from $fieldDefinitionIdentifier field using repository sudo.
     */
    abstract public function getSudoFieldRelationLocation(string $fieldDefinitionIdentifier): ?Location;

    /**
     * Return all related Locations from $fieldDefinitionIdentifier.
     *
     * @return \Netgen\IbexaSiteApi\API\Values\Location[]
     */
    abstract public function getFieldRelationLocations(string $fieldDefinitionIdentifier, int $limit = 25): array;

    /**
     * Return all related Locations from $fieldDefinitionIdentifier using repository sudo.
     *
     * @return \Netgen\IbexaSiteApi\API\Values\Location[]
     */
    abstract public function getSudoFieldRelationLocations(string $fieldDefinitionIdentifier, int $limit = 25): array;

    /**
     * Return related Locations from $fieldDefinitionIdentifier field,
     * optionally limited by a list of $contentTypeIdentifiers.
     *
     * @param string[] $contentTypeIdentifiers
     *
     * @return \Pagerfanta\Pagerfanta Pagerfanta instance iterating over Site API Locations
     */
    abstract public function filterFieldRelationLocations(
        string $fieldDefinitionIdentifier,
        array $contentTypeIdentifiers = [],
        int $maxPerPage = 25,
        int $currentPage = 1,
    ): Pagerfanta;

    /**
     * Return related Locations from $fieldDefinitionIdentifier field using repository sudo,
     * optionally limited by a list of $contentTypeIdentifiers.
     *
     * @param string[] $contentTypeIdentifiers
     *
     * @return \Pagerfanta\Pagerfanta Pagerfanta instance iterating over Site API Locations
     */
    abstract public function filterSudoFieldRelationLocations(
        string $fieldDefinitionIdentifier,
        array $contentTypeIdentifiers = [],
        int $maxPerPage = 25,
        int $currentPage = 1,
    ): Pagerfanta;

    /**
     * Return absolute path for the Content.
     *
     * @see \Netgen\IbexaSiteApi\API\Routing\UrlGenerator::ABSOLUTE_PATH
     */
    abstract public function getPath(array $parameters = []): string;

    /**
     * Return absolute URL for the Content.
     *
     * @see \Netgen\IbexaSiteApi\API\Routing\UrlGenerator::ABSOLUTE_URL
     */
    abstract public function getUrl(array $parameters = []): string;
}
