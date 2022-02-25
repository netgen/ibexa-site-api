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
 * @property string|null $target
 * @property mixed|null $operator
 * @property mixed $value
 */
final class CriterionDefinition extends ValueObject
{
    /**
     * Mandatory name, needed to build a Criterion instance in CriteriaBuilder.
     *
     * @var string
     */
    protected string $name;

    /**
     * Optional target.
     *
     * @var string|null
     */
    protected ?string $target;

    /**
     * Optional operator.
     *
     * @var mixed|null
     */
    protected $operator;

    /**
     * Mandatory value.
     *
     * @var mixed
     */
    protected $value;
}
