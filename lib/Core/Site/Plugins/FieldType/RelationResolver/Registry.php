<?php

declare(strict_types=1);

namespace Netgen\IbexaSiteApi\Core\Site\Plugins\FieldType\RelationResolver;

use OutOfBoundsException;

use function sprintf;

/**
 * Registry for field type relation resolvers.
 *
 * @see \Netgen\IbexaSiteApi\Core\Site\Plugins\FieldType\RelationResolver\Resolver
 */
class Registry
{
    /**
     * Map of resolvers by field type identifier.
     *
     * @var \Netgen\IbexaSiteApi\Core\Site\Plugins\FieldType\RelationResolver\Resolver[]
     */
    protected array $resolverMap = [];

    /**
     * @param \Netgen\IbexaSiteApi\Core\Site\Plugins\FieldType\RelationResolver\Resolver[] $resolverMap
     */
    public function __construct(array $resolverMap = [])
    {
        foreach ($resolverMap as $fieldTypeIdentifier => $resolver) {
            $this->register($fieldTypeIdentifier, $resolver);
        }
    }

    /**
     * Register a $resolver for $fieldTypeIdentifier.
     */
    public function register(string $fieldTypeIdentifier, Resolver $resolver): void
    {
        $this->resolverMap[$fieldTypeIdentifier] = $resolver;
    }

    /**
     * Returns Resolver for $fieldTypeIdentifier.
     */
    public function get(string $fieldTypeIdentifier): Resolver
    {
        return $this->resolverMap[$fieldTypeIdentifier] ?? throw new OutOfBoundsException(
            sprintf(
                "No relation resolver is registered for field type identifier '%s'",
                $fieldTypeIdentifier,
            ),
        );
    }
}
