<?php

declare(strict_types=1);

namespace Netgen\IbexaSiteApi\Core\Site\QueryType;

use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion\Operator;
use InvalidArgumentException;

use function array_key_exists;
use function array_keys;
use function array_merge;
use function is_array;

/**
 * @internal Do not depend on this service, it can be changed without warning.
 *
 * CriterionDefinitionResolver resolves CriterionDefinition instances from the given parameters.
 */
final class CriterionDefinitionResolver
{
    /**
     * Set of available operator names.
     *
     * @var array
     */
    private static array $operatorMap = [
        'eq' => Operator::EQ,
        'gt' => Operator::GT,
        'gte' => Operator::GTE,
        'lt' => Operator::LT,
        'lte' => Operator::LTE,
        'in' => Operator::IN,
        'between' => Operator::BETWEEN,
        'like' => Operator::LIKE,
        'contains' => Operator::CONTAINS,
    ];

    /**
     * Resolve Criterion $parameters.
     *
     * @param mixed $parameters
     *
     * @return \Netgen\IbexaSiteApi\Core\Site\QueryType\CriterionDefinition[]
     *
     * @throws \InvalidArgumentException
     */
    public function resolve(string $name, $parameters): array
    {
        return $this->resolveForTarget($name, null, $parameters);
    }

    /**
     * Resolve Field Criterion $parameters.
     *
     * @return \Netgen\IbexaSiteApi\Core\Site\QueryType\CriterionDefinition[]
     *
     * @throws \InvalidArgumentException
     */
    public function resolveTargets(string $name, array $parameters): array
    {
        $definitionsGrouped = [[]];

        foreach ($parameters as $target => $params) {
            $definitionsGrouped[] = $this->resolveForTarget($name, $target, $params);
        }

        return array_merge(...$definitionsGrouped);
    }

    /**
     * Return CriterionDefinition instances for the given Field $target and its $parameters.
     *
     * @param mixed $parameters
     *
     * @return \Netgen\IbexaSiteApi\Core\Site\QueryType\CriterionDefinition[]
     *
     * @throws \InvalidArgumentException
     */
    private function resolveForTarget(string $name, ?string $target, $parameters): array
    {
        if ($this->isOperatorMap($parameters)) {
            return $this->resolveOperatorMap($name, $target, $parameters);
        }

        return [
            $this->buildDefinition($name, $target, null, $parameters),
        ];
    }

    /**
     * Return CriterionDefinition instances for the given Field $target and its operator $map.
     *
     * @return \Netgen\IbexaSiteApi\Core\Site\QueryType\CriterionDefinition[]
     */
    private function resolveOperatorMap(string $name, ?string $target, array $map): array
    {
        $definitions = [];

        foreach ($map as $operator => $value) {
            $definitions[] = $this->buildDefinitionForOperator($name, $target, $operator, $value);
        }

        return $definitions;
    }

    /**
     * @param mixed $value
     */
    private function buildDefinitionForOperator(string $name, ?string $target, string $operator, $value): CriterionDefinition
    {
        if ($operator === 'not') {
            return $this->buildDefinition(
                'not',
                null,
                null,
                $this->resolveForTarget($name, $target, $value),
            );
        }

        return $this->buildDefinition($name, $target, $operator, $value);
    }

    /**
     * Return CriterionDefinition instance from the given arguments.
     *
     * @param mixed $value
     */
    private function buildDefinition(
        string $name,
        ?string $target,
        ?string $operator,
        $value
    ): CriterionDefinition {
        return new CriterionDefinition([
            'name' => $name,
            'target' => $target,
            'operator' => $this->resolveOperator($operator, $value),
            'value' => $value,
        ]);
    }

    /**
     * Decide if the given $parameters is an operator-value map (otherwise it's a value collection).
     *
     * @param mixed $parameters
     *
     * @throws \InvalidArgumentException
     */
    private function isOperatorMap($parameters): bool
    {
        if (!is_array($parameters)) {
            return false;
        }

        $isOperatorMap = false;
        $isValueCollection = false;

        foreach (array_keys($parameters) as $key) {
            if ($this->isOperator($key)) {
                $isOperatorMap = true;
            } else {
                $isValueCollection = true;
            }
        }

        if ($isOperatorMap && $isValueCollection) {
            throw new InvalidArgumentException(
                'Array of parameters is ambiguous: it should be either an operator map or a value collection',
            );
        }

        return $isOperatorMap;
    }

    private function isOperator($key): bool
    {
        return array_key_exists($key, self::$operatorMap) || $key === 'not';
    }

    /**
     * Resolve actual operator value from the given arguments.
     *
     * @param mixed $value
     */
    private function resolveOperator(?string $symbol, $value)
    {
        if ($symbol === null) {
            return $this->getOperatorByValueType($value);
        }

        return self::$operatorMap[$symbol];
    }

    /**
     * Return operator value by the given $value.
     *
     * @param mixed $value
     *
     * @throws \RuntimeException
     */
    private function getOperatorByValueType($value): string
    {
        if (is_array($value)) {
            return Operator::IN;
        }

        return Operator::EQ;
    }
}
