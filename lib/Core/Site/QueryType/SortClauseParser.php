<?php

declare(strict_types=1);

namespace Netgen\IbexaSiteApi\Core\Site\QueryType;

use Ibexa\Contracts\Core\Repository\Values\Content\Query;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\SortClause;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\SortClause\DateModified;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\SortClause\DatePublished;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\SortClause\Field;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\SortClause\Location\Depth;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\SortClause\Location\Priority;
use InvalidArgumentException;
use Netgen\IbexaSearchExtra\API\Values\Content\Query\SortClause\ContentName;

use function array_key_exists;
use function explode;
use function mb_strtolower;

/**
 * Sort clause parser parses string representation of the SortClause
 * to return the SortClause instance.
 *
 * Supported sort clause strings:
 *
 *  - depth
 *  - depth asc
 *  - depth desc
 *  - field/article/title
 *  - field/article/title asc
 *  - field/article/title desc
 *  - modified
 *  - modified asc
 *  - modified desc
 *  - name
 *  - name asc
 *  - name desc
 *  - priority
 *  - priority asc
 *  - priority desc
 *  - published
 *  - published asc
 *  - published desc
 *
 * @internal do not depend on this service, it can be changed without warning
 */
final class SortClauseParser
{
    /**
     * Return new sort clause instance by the given $definition string.
     *
     * @throws \InvalidArgumentException
     */
    public function parse(string $definition): SortClause
    {
        $values = explode(' ', $definition);
        $direction = $this->getDirection($values);
        $values = explode('/', $values[0]);
        $type = $values[0];

        switch (mb_strtolower($type)) {
            case 'depth':
                return new Depth($direction);

            case 'field':
                return $this->buildFieldSortClause($values, $direction);

            case 'modified':
                return new DateModified($direction);

            case 'name':
                return new ContentName($direction);

            case 'priority':
                return new Priority($direction);

            case 'published':
                return new DatePublished($direction);
        }

        throw new InvalidArgumentException(
            "Could not handle sort type '{$type}'",
        );
    }

    /**
     * Build a new Field sort clause from the given arguments.
     *
     * @param mixed $direction
     *
     * @throws \InvalidArgumentException
     */
    private function buildFieldSortClause(array $values, $direction): Field
    {
        if (!array_key_exists(1, $values)) {
            throw new InvalidArgumentException(
                'Field sort clause requires ContentType identifier',
            );
        }

        if (!array_key_exists(2, $values)) {
            throw new InvalidArgumentException(
                'Field sort clause requires FieldDefinition identifier',
            );
        }

        return new Field($values[1], $values[2], $direction);
    }

    /**
     * Resolve direction constant value from the given array of $values.
     *
     * @param string[] $values
     *
     * @throws \InvalidArgumentException
     */
    private function getDirection(array $values): string
    {
        $direction = 'asc';

        if (array_key_exists(1, $values)) {
            $direction = $values[1];
        }

        switch (mb_strtolower($direction)) {
            case 'asc':
                return Query::SORT_ASC;

            case 'desc':
                return Query::SORT_DESC;
        }

        throw new InvalidArgumentException(
            "Could not handle sort direction '{$direction}'",
        );
    }
}
