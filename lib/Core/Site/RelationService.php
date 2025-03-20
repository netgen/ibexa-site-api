<?php

declare(strict_types=1);

namespace Netgen\IbexaSiteApi\Core\Site;

use Exception;
use Ibexa\Contracts\Core\Repository\Values\Content\LocationQuery;
use Ibexa\Contracts\Core\Repository\Values\Content\Query;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion;
use Netgen\IbexaSiteApi\API\RelationService as RelationServiceInterface;
use Netgen\IbexaSiteApi\API\Site as SiteInterface;
use Netgen\IbexaSiteApi\API\Values\Content;
use Netgen\IbexaSiteApi\API\Values\Location;
use Netgen\IbexaSiteApi\Core\Site\Plugins\FieldType\RelationResolver\Registry as RelationResolverRegistry;
use Netgen\IbexaSiteApi\Core\Traits\SearchResultExtractorTrait;
use Psr\Log\LoggerInterface;

use function array_filter;
use function array_flip;
use function array_slice;
use function in_array;
use function sprintf;
use function usort;

/**
 * @final
 *
 * @internal
 *
 * Hint against API interface instead of this service:
 *
 * @see \Netgen\IbexaSiteApi\API\RelationService
 */
class RelationService implements RelationServiceInterface
{
    use SearchResultExtractorTrait;

    public function __construct(
        private readonly SiteInterface $site,
        private readonly RelationResolverRegistry $relationResolverRegistry,
        private readonly LoggerInterface $logger,
    ) {}

    public function loadFieldRelation(
        Content $content,
        string $fieldDefinitionIdentifier,
        array $contentTypeIdentifiers = [],
    ): ?Content {
        $relatedContentItems = $this->loadFieldRelations(
            $content,
            $fieldDefinitionIdentifier,
            $contentTypeIdentifiers,
        );

        return $relatedContentItems[0] ?? null;
    }

    public function loadFieldRelations(
        Content $content,
        string $fieldDefinitionIdentifier,
        array $contentTypeIdentifiers = [],
        ?int $limit = null,
    ): array {
        $field = $content->getField($fieldDefinitionIdentifier);
        $relationResolver = $this->relationResolverRegistry->get($field->fieldTypeIdentifier);

        $relatedContentIds = $relationResolver->getRelationIds($field);
        $relatedContentItems = $this->getRelatedContentItems($relatedContentIds, $contentTypeIdentifiers);
        $this->sortByIdOrder($relatedContentItems, $relatedContentIds);

        if ($limit !== null) {
            return array_slice($relatedContentItems, 0, $limit);
        }

        return $relatedContentItems;
    }

    public function loadFieldRelationLocation(
        Content $content,
        string $fieldDefinitionIdentifier,
        array $contentTypeIdentifiers = [],
    ): ?Location {
        $relatedLocations = $this->loadFieldRelationLocations(
            $content,
            $fieldDefinitionIdentifier,
            $contentTypeIdentifiers,
        );

        return $relatedLocations[0] ?? null;
    }

    public function loadFieldRelationLocations(
        Content $content,
        string $fieldDefinitionIdentifier,
        array $contentTypeIdentifiers = [],
        ?int $limit = null,
    ): array {
        $field = $content->getField($fieldDefinitionIdentifier);
        $relationResolver = $this->relationResolverRegistry->get($field->fieldTypeIdentifier);

        $relatedContentIds = $relationResolver->getRelationIds($field);
        $relatedContentItems = $this->getRelatedContentItems($relatedContentIds, $contentTypeIdentifiers);
        $this->sortByIdOrder($relatedContentItems, $relatedContentIds);

        $relatedLocations = [];

        foreach ($relatedContentItems as $relatedContentItem) {
            try {
                if ($relatedContentItem->mainLocationId === null) {
                    $this->logger->debug(
                        sprintf(
                            'Could not load related Location: Content #%s has no Locations',
                            $relatedContentItem->id,
                        ),
                    );

                    continue;
                }

                $relatedLocations[] = $relatedContentItem->mainLocation;
            } catch (Exception $exception) {
                $this->logger->debug(
                    sprintf(
                        'Could not load related Location #%s: %s',
                        $relatedContentItem->mainLocationId,
                        $exception->getMessage(),
                    ),
                );
            }
        }

        $locations = array_filter(
            $relatedLocations,
            fn (Location $location): bool => $this->site->getSettings()->showHiddenItems || $location->isVisible,
        );

        if ($limit !== null) {
            $locations = array_slice($locations, 0, $limit);
        }

        return $locations;
    }

    public function loadReverseFieldRelations(
        Content $content,
        string $fieldDefinitionIdentifier,
        array $contentTypeIdentifiers = [],
        ?int $limit = null,
    ): array {
        $query = new Query();

        $criteria = [
            new Criterion\FieldRelation($fieldDefinitionIdentifier, Criterion\Operator::IN, [$content->id])
        ];

        if (count($contentTypeIdentifiers) > 0) {
            $criteria[] = new Criterion\ContentTypeIdentifier($contentTypeIdentifiers);
        }

        $query->filter = new Criterion\LogicalAnd($criteria);

        if ($limit !== null && $limit > 0) {
            $query->limit = $limit;
        }

        $result = $this->site->getFindService()->findContent($query);

        return $this->extractContentItems($result);
    }

    public function loadReverseFieldRelationLocations(
        Content $content,
        string $fieldDefinitionIdentifier,
        array $contentTypeIdentifiers = [],
        ?int $limit = null,
    ): array {
        $query = new LocationQuery();

        $criteria = [
            new Criterion\FieldRelation($fieldDefinitionIdentifier, Criterion\Operator::IN, [$content->id]),
            new Criterion\Location\IsMainLocation(Criterion\Location\IsMainLocation::MAIN),
        ];

        if (count($contentTypeIdentifiers) > 0) {
            $criteria[] = new Criterion\ContentTypeIdentifier($contentTypeIdentifiers);
        }

        $query->filter = new Criterion\LogicalAnd($criteria);

        if ($limit !== null && $limit > 0) {
            $query->limit = $limit;
        }

        $result = $this->site->getFindService()->findLocations($query);

        return $this->extractLocations($result);
    }

    /**
     * Return an array of related Content items.
     *
     * @return \Netgen\IbexaSiteApi\API\Values\Content[]
     */
    private function getRelatedContentItems(array $relatedContentIds, array $contentTypeIdentifiers): array
    {
        if (empty($relatedContentIds)) {
            return [];
        }

        $contentItems = [];

        foreach ($relatedContentIds as $contentId) {
            try {
                $content = $this->site->getLoadService()->loadContent($contentId);
            } catch (Exception $exception) {
                $this->logger->debug(
                    sprintf(
                        'Could not load related Content #%s: %s',
                        $contentId,
                        $exception->getMessage(),
                    ),
                );

                continue;
            }

            if (!empty($contentTypeIdentifiers) && !$this->isContentOfType($content, $contentTypeIdentifiers)) {
                continue;
            }

            if (!$content->isVisible && !$this->site->getSettings()->showHiddenItems) {
                continue;
            }

            $contentItems[] = $content;
        }

        return $contentItems;
    }

    private function isContentOfType(Content $content, array $contentTypeIdentifiers): bool
    {
        return in_array($content->contentInfo->contentTypeIdentifier, $contentTypeIdentifiers, true);
    }

    /**
     * Sorts $relatedContentItems to match order from $relatedContentIds.
     */
    private function sortByIdOrder(array &$relatedContentItems, array $relatedContentIds): void
    {
        $sortedIdList = array_flip($relatedContentIds);

        $sorter = static fn (Content $content1, Content $content2): int => $sortedIdList[$content1->id] <=> $sortedIdList[$content2->id];

        usort($relatedContentItems, $sorter);
    }
}
