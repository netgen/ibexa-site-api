<?php

declare(strict_types=1);

namespace Netgen\IbexaSiteApi\Core\Site\QueryType;

use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion\ContentTypeIdentifier;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion\DateMetadata;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion\Field;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion\IsFieldEmpty;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion\Location\Depth;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion\Location\IsMainLocation;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion\Location\Priority;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion\LogicalAnd;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion\LogicalNot;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion\ParentLocationId;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion\Subtree;
use InvalidArgumentException;
use Netgen\IbexaSearchExtra\API\Values\Content\Query\Criterion\ObjectStateIdentifier;
use Netgen\IbexaSearchExtra\API\Values\Content\Query\Criterion\SectionIdentifier;
use Netgen\IbexaSearchExtra\API\Values\Content\Query\Criterion\Visible;

use function count;
use function is_array;
use function is_int;
use function reset;
use function strtotime;

/**
 * @internal Do not depend on this service, it can be changed without warning.
 *
 * CriteriaBuilder builds criteria from CriterionDefinition instances.
 *
 * @see \Netgen\IbexaSiteApi\Core\Site\QueryType\CriterionDefinition
 */
final class CriteriaBuilder
{
    /**
     * Build criteria for the given array of criterion $definitions.
     *
     * @param \Netgen\IbexaSiteApi\Core\Site\QueryType\CriterionDefinition[] $definitions
     *
     * @return \Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion[]
     */
    public function build(array $definitions): array
    {
        $criteria = [];

        foreach ($definitions as $definition) {
            $criterion = $this->dispatchBuild($definition);

            if ($criterion instanceof Criterion) {
                $criteria[] = $criterion;
            }
        }

        return $criteria;
    }

    /**
     * Build criterion $name from the given criterion $definition.
     */
    private function dispatchBuild(CriterionDefinition $definition): ?Criterion
    {
        switch ($definition->name) {
            case 'content_type':
                return $this->buildContentTypeIdentifier($definition);

            case 'depth':
                return $this->buildDepth($definition);

            case 'field':
                return $this->buildField($definition);

            case 'main':
                return $this->buildIsMainLocation($definition);

            case 'not':
                return $this->buildLogicalNot($definition);

            case 'parent_location_id':
                return $this->buildParentLocationId($definition);

            case 'priority':
                return $this->buildPriority($definition);

            case 'creation_date':
                return $this->buildDateMetadataCreated($definition);

            case 'modification_date':
                return $this->buildDateMetadataModified($definition);

            case 'section':
                return $this->buildSection($definition);

            case 'state':
                return $this->buildObjectState($definition);

            case 'subtree':
                return $this->buildSubtree($definition);

            case 'visible':
                return $this->buildVisible($definition);

            case 'is_field_empty':
                return $this->buildIsFieldEmpty($definition);
        }

        throw new InvalidArgumentException(
            "Criterion named '{$definition->name}' is not handled",
        );
    }

    private function buildContentTypeIdentifier(CriterionDefinition $definition): ?ContentTypeIdentifier
    {
        if ($definition->value === null) {
            return null;
        }

        return new ContentTypeIdentifier($definition->value);
    }

    private function buildDepth(CriterionDefinition $definition): ?Depth
    {
        if ($definition->value === null) {
            return null;
        }

        return new Depth($definition->operator, $definition->value);
    }

    private function buildField(CriterionDefinition $definition): Field
    {
        return new Field(
            $definition->target,
            $definition->operator,
            $definition->value,
        );
    }

    private function buildIsMainLocation(CriterionDefinition $definition): ?IsMainLocation
    {
        if ($definition->value === null) {
            return null;
        }

        $isMainLocation = $definition->value ? IsMainLocation::MAIN : IsMainLocation::NOT_MAIN;

        return new IsMainLocation($isMainLocation);
    }

    private function buildLogicalNot(CriterionDefinition $definition): ?LogicalNot
    {
        if ($definition->value === null) {
            return null;
        }

        $criteria = $this->build($definition->value);
        $criterion = $this->reduceCriteria($criteria);

        return new LogicalNot($criterion);
    }

    private function reduceCriteria(array $criteria): Criterion
    {
        if (count($criteria) === 1) {
            return reset($criteria);
        }

        return new LogicalAnd($criteria);
    }

    private function buildParentLocationId(CriterionDefinition $definition): ?ParentLocationId
    {
        if ($definition->value === null) {
            return null;
        }

        return new ParentLocationId($definition->value);
    }

    private function buildPriority(CriterionDefinition $definition): ?Priority
    {
        if ($definition->value === null) {
            return null;
        }

        return new Priority($definition->operator, $definition->value);
    }

    private function buildDateMetadataCreated(CriterionDefinition $definition): DateMetadata
    {
        return new DateMetadata(
            DateMetadata::CREATED,
            $definition->operator,
            $this->resolveTimeValues($definition->value),
        );
    }

    private function buildDateMetadataModified(CriterionDefinition $definition): DateMetadata
    {
        return new DateMetadata(
            DateMetadata::MODIFIED,
            $definition->operator,
            $this->resolveTimeValues($definition->value),
        );
    }

    private function buildSection(CriterionDefinition $definition): ?SectionIdentifier
    {
        if ($definition->value === null) {
            return null;
        }

        return new SectionIdentifier($definition->value);
    }

    private function buildObjectState(CriterionDefinition $definition): ObjectStateIdentifier
    {
        return new ObjectStateIdentifier($definition->target, $definition->value);
    }

    private function buildSubtree(CriterionDefinition $definition): ?Subtree
    {
        if ($definition->value === null) {
            return null;
        }

        return new Subtree($definition->value);
    }

    private function resolveTimeValues(mixed $valueOrValues): int|array
    {
        if (!is_array($valueOrValues)) {
            return $this->resolveTimeValue($valueOrValues);
        }

        $returnValues = [];

        foreach ($valueOrValues as $key => $value) {
            $returnValues[$key] = $this->resolveTimeValue($value);
        }

        return $returnValues;
    }

    private function resolveTimeValue(int|string $value): int
    {
        if (is_int($value)) {
            return $value;
        }

        $timestamp = strtotime($value);

        if ($timestamp === false) {
            throw new InvalidArgumentException(
                "'{$value}' is invalid time string",
            );
        }

        return $timestamp;
    }

    private function buildVisible(CriterionDefinition $definition): ?Visible
    {
        if ($definition->value === null) {
            return null;
        }

        return new Visible($definition->value);
    }

    private function buildIsFieldEmpty(CriterionDefinition $definition): ?IsFieldEmpty
    {
        if ($definition->value === null) {
            return null;
        }

        return new IsFieldEmpty((string) $definition->target, (bool) $definition->value);
    }
}
