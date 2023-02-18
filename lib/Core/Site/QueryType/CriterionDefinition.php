<?php

declare(strict_types=1);

namespace Netgen\IbexaSiteApi\Core\Site\QueryType;

use Ibexa\Contracts\Core\Repository\Values\ValueObject;

/**
 * Holds resolved values of parameters defining a criterion: name, target, operator and value.
 *
 * @see \Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion
 * @see \Netgen\IbexaSiteApi\Core\Site\QueryType\CriterionDefinitionResolver
 * @see \Netgen\IbexaSiteApi\Core\Site\QueryType\CriteriaBuilder
 *
 * @property string $name
 * @property ?string $target
 * @property mixed|null $operator
 * @property mixed $value
 */
final class CriterionDefinition extends ValueObject
{
    /**
     * Mandatory name, needed to build a Criterion instance in CriteriaBuilder.
     */
    protected string $name;

    /**
     * Optional target.
     */
    protected ?string $target;

    /**
     * Optional operator.
     */
    protected mixed $operator;

    /**
     * Mandatory value.
     */
    protected mixed $value;
}
