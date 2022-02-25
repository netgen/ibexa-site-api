<?php

declare(strict_types=1);

namespace Netgen\IbexaSiteApi\Core\Site\QueryType;

use Ibexa\Contracts\Core\Repository\Values\Content\LocationQuery;
use Ibexa\Contracts\Core\Repository\Values\Content\Query;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Base implementation for Location search QueryTypes.
 */
abstract class Location extends Base
{
    final protected function configureBaseOptions(OptionsResolver $resolver): void
    {
        parent::configureBaseOptions($resolver);

        $resolver->setDefined([
            'depth',
            'main',
            'parent_location_id',
            'priority',
            'subtree',
        ]);

        $resolver->setAllowedTypes('parent_location_id', ['null', 'int', 'string', 'array']);
        $resolver->setAllowedTypes('subtree', ['null', 'string', 'array']);
        $resolver->setAllowedTypes('depth', ['null', 'int', 'array']);
        $resolver->setAllowedTypes('priority', ['null', 'int', 'array']);

        $resolver->setAllowedValues('main', [true, false, null]);
    }

    protected function buildQuery(): Query
    {
        return new LocationQuery();
    }
}
