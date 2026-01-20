<?php

declare(strict_types=1);

namespace Netgen\IbexaSiteApi\Core\Site\Plugins\FieldType\RelationResolver\Resolver;

use Ibexa\Contracts\Core\FieldType\Value;
use Netgen\IbexaSiteApi\Core\Site\Plugins\FieldType\RelationResolver\Resolver;

/**
 * Relation field type relation Resolver.
 *
 * @see \Ibexa\Core\FieldType\Relation\Type
 */
class Relation extends Resolver
{
    protected function getSupportedFieldTypeIdentifier(): string
    {
        return 'ibexa_object_relation';
    }

    protected function getRelationIdsFromValue(Value $value): array
    {
        /** @var \Ibexa\Core\FieldType\Relation\Value $value */
        if ($value->destinationContentId === null) {
            return [];
        }

        return [$value->destinationContentId];
    }
}
