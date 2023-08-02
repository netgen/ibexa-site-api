<?php

declare(strict_types=1);

namespace Netgen\IbexaSiteApi\Core\Site\Plugins\FieldType\RelationResolver\Resolver;

use Ibexa\Contracts\Core\FieldType\Value;
use Netgen\IbexaSiteApi\Core\Site\Plugins\FieldType\RelationResolver\Resolver;

/**
 * Netgen EnhancedLink field type relation resolver.
 *
 * @see \Netgen\IbexaFieldTypeEnhancedLink\FieldType\Type
 */
class EnhancedLink extends Resolver
{
    protected function getSupportedFieldTypeIdentifier(): string
    {
        return 'ngenhancedlink';
    }

    protected function getRelationIdsFromValue(Value $value): array
    {
        /* @var \Netgen\IbexaFieldTypeEnhancedLink\FieldType\Value $value */

        if ($value->isTypeInternal()) {
            return [$value->reference];
        }

        return [];
    }
}
