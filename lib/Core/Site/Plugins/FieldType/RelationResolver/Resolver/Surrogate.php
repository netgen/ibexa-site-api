<?php

declare(strict_types=1);

namespace Netgen\IbexaSiteApi\Core\Site\Plugins\FieldType\RelationResolver\Resolver;

use Ibexa\Contracts\Core\FieldType\Value;
use Netgen\IbexaSiteApi\Core\Site\Plugins\FieldType\RelationResolver\Resolver;

/**
 * Surrogate field type relation Resolver.
 *
 * This resolver will match field type with identifier 'ngsurrogate', returned when nonexistent field is
 * requested from Content.
 */
class Surrogate extends Resolver
{
    protected function getSupportedFieldTypeIdentifier(): string
    {
        return 'ngsurrogate';
    }

    protected function getRelationIdsFromValue(Value $value): array
    {
        return [];
    }
}
