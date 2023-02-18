<?php

declare(strict_types=1);

namespace Netgen\IbexaSiteApi\API\Values;

use Ibexa\Contracts\Core\Repository\Values\ValueObject;

/**
 * Site Field represents a field of a Site Content object.
 *
 * Corresponds to Ibexa Repository Field object.
 *
 * @see \Ibexa\Contracts\Core\Repository\Values\Content\Field
 *
 * @property-read int $id
 * @property-read string $fieldDefIdentifier
 * @property-read \Ibexa\Contracts\Core\FieldType\Value $value
 * @property-read string $languageCode
 * @property-read string $fieldTypeIdentifier
 * @property-read ?string $name
 * @property-read ?string $description
 * @property-read \Netgen\IbexaSiteApi\API\Values\Content $content
 * @property-read \Ibexa\Contracts\Core\Repository\Values\Content\Field $innerField
 * @property-read \Ibexa\Contracts\Core\Repository\Values\ContentType\FieldDefinition $innerFieldDefinition
 */
abstract class Field extends ValueObject
{
    abstract public function isEmpty(): bool;

    /**
     * Returns whether the field is of 'ngsurrogate' type, returned when nonexistent field is requested from Content.
     */
    abstract public function isSurrogate(): bool;
}
