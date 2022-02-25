<?php

declare(strict_types=1);

namespace Netgen\IbexaSiteApi\Core\Site\Plugins\FieldType\RelationResolver;

use Ibexa\Contracts\Core\FieldType\Value;
use LogicException;
use Netgen\IbexaSiteApi\API\Values\Field;

/**
 * Field type relation resolver returns related Content IDs for a Content field
 * of a specific field type.
 *
 * If a field type is to be used for relations, it needs a dedicated implementation
 * of this class to be registered with resolver registry.
 *
 * @see \Netgen\Bundle\IbexaSiteApiBundle\DependencyInjection\Compiler\RelationResolverRegistrationPass
 */
abstract class Resolver
{
    /**
     * Return related Content IDs for the given $field.
     *
     * @throws \LogicException If the field can't be handled by the resolver
     *
     * @return int[]|string[]
     */
    public function getRelationIds(Field $field): array
    {
        if (!$this->accept($field)) {
            $identifier = $this->getSupportedFieldTypeIdentifier();

            throw new LogicException(
                "This resolver can only handle fields of '{$identifier}' type"
            );
        }

        return $this->getRelationIdsFromValue($field->value);
    }

    /**
     * Return accepted field type identifier.
     */
    abstract protected function getSupportedFieldTypeIdentifier(): string;

    /**
     * Return related Content IDs for the given $field value.
     *
     * @return int[]|string[]
     */
    abstract protected function getRelationIdsFromValue(Value $value): array;

    /**
     * Check if the given $field is of the accepted field type.
     */
    protected function accept(Field $field): bool
    {
        return $field->fieldTypeIdentifier === $this->getSupportedFieldTypeIdentifier();
    }
}
