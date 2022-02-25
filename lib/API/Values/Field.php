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
 * @property int|string $id
 * @property string $fieldDefIdentifier
 * @property \Ibexa\Contracts\Core\FieldType\Value $value
 * @property string $languageCode
 * @property string $fieldTypeIdentifier
 * @property string $name
 * @property string $description
 * @property \Netgen\IbexaSiteApi\API\Values\Content $content
 * @property \Ibexa\Contracts\Core\Repository\Values\Content\Field $innerField
 * @property \Ibexa\Contracts\Core\Repository\Values\ContentType\FieldDefinition $innerFieldDefinition
 */
abstract class Field extends ValueObject
{
    abstract public function isEmpty(): bool;

    /**
     * Returns whether the field is of 'ngsurrogate' type, returned when nonexistent field is requested from Content.
     */
    abstract public function isSurrogate(): bool;
}
