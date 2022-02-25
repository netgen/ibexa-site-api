<?php

declare(strict_types=1);

namespace Netgen\IbexaSiteApi\Core\Site\QueryType;

use Ibexa\Contracts\Core\Repository\Values\Content\Query;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Base implementation for Content search QueryTypes.
 */
abstract class Content extends Base
{
    final protected function configureBaseOptions(OptionsResolver $resolver): void
    {
        parent::configureBaseOptions($resolver);
    }

    protected function buildQuery(): Query
    {
        return new Query();
    }
}
