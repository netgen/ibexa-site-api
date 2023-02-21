<?php

declare(strict_types=1);

namespace Netgen\Bundle\IbexaSiteApiBundle\QueryType;

use OutOfBoundsException;

use function sprintf;

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
    private array $queryDefinitionMap = [];

    /**
     * Add $queryDefinition by $name to the internal map.
     */
    public function add(string $name, QueryDefinition $queryDefinition): void
    {
        $this->queryDefinitionMap[$name] = $queryDefinition;
    }

    /**
     * Return QueryDefinition by given $name.
     *
     * @throws \OutOfBoundsException if no QueryDefinition with given $name is found
     */
    public function get(string $name): QueryDefinition
    {
        return $this->queryDefinitionMap[$name] ?? throw new OutOfBoundsException(
            sprintf(
                "Could not find QueryDefinition with name '%s'",
                $name,
            ),
        );
    }
}
