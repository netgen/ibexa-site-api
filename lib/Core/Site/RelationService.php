<?php

declare(strict_types=1);

namespace Netgen\IbexaSiteApi\Core\Site;

use Netgen\IbexaSiteApi\API\RelationService as RelationServiceInterface;
use Netgen\IbexaSiteApi\API\Site as SiteInterface;
use Netgen\IbexaSiteApi\API\Values\Content;
use Netgen\IbexaSiteApi\API\Values\Location;
use Netgen\IbexaSiteApi\Core\Site\Plugins\FieldType\RelationResolver\Registry as RelationResolverRegistry;
use Netgen\IbexaSiteApi\Core\Traits\SearchResultExtractorTrait;
use Throwable;

use function array_filter;
use function array_flip;
use function array_slice;
use function in_array;
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
        private readonly RelationResolverRegistry $relationResolverRegistry
    ) {
    }

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
                $relatedLocations[] = $relatedContentItem->mainLocation;
            } catch (Throwable $throwable) {
                // do nothing
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
            } catch (Throwable) {
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
