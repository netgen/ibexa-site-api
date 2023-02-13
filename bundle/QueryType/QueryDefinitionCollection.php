<?php

declare(strict_types=1);

namespace Netgen\Bundle\IbexaSiteApiBundle\QueryType;

use OutOfBoundsException;

use function array_key_exists;

/**
 * QueryDefinitionCollection contains a map of QueryDefinitions by their name string.
 *
 * @see \Netgen\Bundle\IbexaSiteApiBundle\QueryType\QueryDefinition
 *
 * @internal do not depend on this service, it can be changed without warning
 */
final class QueryDefinitionCollection
{
    /**
     * Internal map of QueryDefinitions.
     *
     * @var \Netgen\Bundle\IbexaSiteApiBundle\QueryType\QueryDefinition[]
     */
    private $queryDefinitionMap = [];

    /**
     * Add $queryDefinition by $name to the internal map.
     *
     * @param \Netgen\Bundle\IbexaSiteApiBundle\QueryType\QueryDefinition $queryDefinition
     */
    public function add(string $name, QueryDefinition $queryDefinition): void
    {
        $this->queryDefinitionMap[$name] = $queryDefinition;
    }

    /**
     * Return QueryDefinition by given $name.
     *
     * @return \Netgen\Bundle\IbexaSiteApiBundle\QueryType\QueryDefinition
     *
     * @throws \OutOfBoundsException if no QueryDefinition with given $name is found
     */
    public function get(string $name): QueryDefinition
    {
        if (array_key_exists($name, $this->queryDefinitionMap)) {
            return $this->queryDefinitionMap[$name];
        }

        throw new OutOfBoundsException(
            "Could not find QueryDefinition with name '{$name}'",
        );
    }
}
