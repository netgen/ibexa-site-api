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
 * @property int $id
 * @property ?int $mainLocationId
 * @property string $name
 * @property string $languageCode
 * @property bool $isVisible
 * @property \Netgen\IbexaSiteApi\API\Values\ContentInfo $contentInfo
 * @property \Netgen\IbexaSiteApi\API\Values\Field[]|\Netgen\IbexaSiteApi\API\Values\Fields $fields
 * @property ?\Netgen\IbexaSiteApi\API\Values\Location $mainLocation
 * @property ?\Netgen\IbexaSiteApi\API\Values\Content $owner
 * @property ?\Ibexa\Contracts\Core\Repository\Values\User\User $innerOwnerUser
 * @property \Ibexa\Contracts\Core\Repository\Values\Content\Content $innerContent
 * @property \Ibexa\Contracts\Core\Repository\Values\Content\VersionInfo $innerVersionInfo
 * @property \Ibexa\Contracts\Core\Repository\Values\Content\VersionInfo $versionInfo
 */
abstract class Content extends ValueObject
{
    /**
     * Returns if content has the field with the given field definition $identifier.
     */
    abstract public function hasField(string $identifier): bool;

    /**
     * Return the Field object for the given field definition $identifier.
     */
    abstract public function getField(string $identifier): Field;

    /**
     * Returns if content has the field with the given field $id.
     *
     * @param int $id
     */
    abstract public function hasFieldById(int $id): bool;

    /**
     * Return Field object for the given field $id.
     *
     * @param int $id
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
     *
     * @param int $id
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
     * Return all related Content from $fieldDefinitionIdentifier.
     *
     * @return \Netgen\IbexaSiteApi\API\Values\Content[]
     */
    abstract public function getFieldRelations(string $fieldDefinitionIdentifier, int $limit = 25): array;

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
        int $currentPage = 1
    ): Pagerfanta;

    /**
     * Return single related Location from $fieldDefinitionIdentifier field.
     */
    abstract public function getFieldRelationLocation(string $fieldDefinitionIdentifier): ?Location;

    /**
     * Return all related Locations from $fieldDefinitionIdentifier.
     *
     * @param string $fieldDefinitionIdentifier
     * @param int $limit
     *
     * @return \Netgen\IbexaSiteApi\API\Values\Location[]
     */
    abstract public function getFieldRelationLocations(string $fieldDefinitionIdentifier, int $limit = 25): array;

    /**
     * Return related Locations from $fieldDefinitionIdentifier field,
     * optionally limited by a list of $contentTypeIdentifiers.
     *
     * @param string $fieldDefinitionIdentifier
     * @param string[] $contentTypeIdentifiers
     * @param int $maxPerPage
     * @param int $currentPage
     *
     * @return \Pagerfanta\Pagerfanta Pagerfanta instance iterating over Site API Locations
     */
    abstract public function filterFieldRelationLocations(
        string $fieldDefinitionIdentifier,
        array $contentTypeIdentifiers = [],
        int $maxPerPage = 25,
        int $currentPage = 1
    ): Pagerfanta;
}
