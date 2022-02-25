<?php

declare(strict_types=1);

namespace Netgen\IbexaSiteApi\Core\Site\Plugins\FieldType\RelationResolver\Resolver;

use Ibexa\Contracts\Core\FieldType\Value;
use Netgen\IbexaSiteApi\Core\Site\Plugins\FieldType\RelationResolver\Resolver;

/**
 * RelationList field type relation resolver.
 *
 * @see \Ibexa\Core\FieldType\RelationList
 */
class RelationList extends Resolver
{
    protected function getSupportedFieldTypeIdentifier(): string
    {
        return 'ezobjectrelationlist';
    }

    protected function getRelationIdsFromValue(Value $value): array
    {
        /* @var \Ibexa\Core\FieldType\RelationList\Value $value */
        return $value->destinationContentIds;
    }
}
