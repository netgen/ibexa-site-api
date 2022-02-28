<?php

declare(strict_types=1);

namespace Netgen\IbexaSiteApi\API\Values;

use ArrayAccess;
use Countable;
use IteratorAggregate;

/**
 * Collection of Content Fields, accessible as an array with FieldDefinition identifier as Field's key.
 *
 * @see \Netgen\IbexaSiteApi\API\Values\Field
 */
abstract class Fields implements IteratorAggregate, ArrayAccess, Countable
{
    /**
     * Return whether the collection contains a field with the given $identifier.
     */
    abstract public function hasField(string $identifier): bool;

    /**
     * Return the field with the given $identifier.
     *
     * @return \Netgen\IbexaSiteApi\API\Values\Field
     */
    abstract public function getField(string $identifier): Field;

    /**
     * Return whether the collection contains a field with the given $id.
     *
     * @param int $id
     */
    abstract public function hasFieldById(int $id): bool;

    /**
     * Return the field with the given $id.
     *
     * @param int $id
     */
    abstract public function getFieldById(int $id): Field;

    /**
     * Return first existing and non-empty field by the given $firstIdentifier and $identifiers.
     *
     * If no field is found in the Content, a surrogate field will be returned.
     * If all found fields are empty, the first found field will be returned.
     *
     * @param string ...$otherIdentifiers
     */
    abstract public function getFirstNonEmptyField(string $firstIdentifier, string ...$otherIdentifiers): Field;
}
