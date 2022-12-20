<?php

declare(strict_types=1);

namespace Netgen\IbexaSiteApi\Core\Site\Values;

use Ibexa\Contracts\Core\FieldType\Value;
use Ibexa\Contracts\Core\Repository\ContentService;
use Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException;
use Ibexa\Contracts\Core\Repository\Repository;
use Ibexa\Contracts\Core\Repository\UserService;
use Ibexa\Contracts\Core\Repository\Values\Content\Content as RepoContent;
use Ibexa\Contracts\Core\Repository\Values\Content\LocationQuery;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion\ContentId;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion\LogicalAnd;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\SortClause\Location\Path as PathSortClause;
use Ibexa\Contracts\Core\Repository\Values\Content\VersionInfo;
use Ibexa\Contracts\Core\Repository\Values\User\User;
use Netgen\IbexaSearchExtra\API\Values\Content\Query\Criterion\Visible;
use Netgen\IbexaSiteApi\API\Site;
use Netgen\IbexaSiteApi\API\Values\Content as APIContent;
use Netgen\IbexaSiteApi\API\Values\ContentInfo as APIContentInfo;
use Netgen\IbexaSiteApi\API\Values\Field as APIField;
use Netgen\IbexaSiteApi\API\Values\Location as APILocation;
use Netgen\IbexaSiteApi\API\Values\Path;
use Netgen\IbexaSiteApi\API\Values\Url;
use Netgen\IbexaSiteApi\Core\Site\DomainObjectMapper;
use Netgen\IbexaSiteApi\Core\Site\Pagination\Pagerfanta\FilterAdapter;
use Pagerfanta\Adapter\ArrayAdapter;
use Pagerfanta\Pagerfanta;
use Psr\Log\LoggerInterface;

/**
 * @internal hint against API Content instead of this class
 *
 * @see \Netgen\IbexaSiteApi\API\Values\Content
 */
final class Content extends APIContent
{
    protected int $id;
    protected ?string $name;
    protected string $languageCode;
    protected ?int $mainLocationId;

    private ?APIContent $owner = null;
    private ?User $innerOwnerUser = null;
    private ?APIContent $modifier = null;
    private ?User $innerModifierUser = null;
    private ?APIContentInfo $contentInfo = null;
    private ?RepoContent $innerContent = null;
    private ?APILocation $internalMainLocation = null;
    private ?Path $path = null;
    private ?Url $url = null;

    private Site $site;
    private DomainObjectMapper $domainObjectMapper;
    private ContentService $contentService;
    private UserService $userService;
    private Repository $repository;
    private Fields $fields;
    private VersionInfo $innerVersionInfo;

    private bool $isOwnerInitialized = false;
    private bool $isInnerOwnerUserInitialized = false;
    private bool $isModifierInitialized = false;
    private bool $isInnerModifierUserInitialized = false;

    public function __construct(array $properties, bool $failOnMissingField, LoggerInterface $logger)
    {
        $this->site = $properties['site'];
        $this->domainObjectMapper = $properties['domainObjectMapper'];
        $this->contentService = $properties['repository']->getContentService();
        $this->userService = $properties['repository']->getUserService();
        $this->repository = $properties['repository'];
        $this->innerVersionInfo = $properties['innerVersionInfo'];
        $this->languageCode = $properties['languageCode'];
        $this->name = $properties['name'];
        $this->mainLocationId = $properties['mainLocationId'];
        $this->id = $properties['id'];

        $this->fields = new Fields($this, $this->domainObjectMapper, $failOnMissingField, $logger);

        unset(
            $properties['site'],
            $properties['domainObjectMapper'],
            $properties['repository'],
            $properties['innerVersionInfo'],
            $properties['languageCode'],
            $properties['name'],
            $properties['mainLocationId'],
            $properties['id'],
        );

        parent::__construct($properties);
    }

    /**
     * {@inheritdoc}
     *
     * Magic getter for retrieving convenience properties.
     *
     * @param string $property The name of the property to retrieve
     */
    public function __get($property)
    {
        return match ($property) {
            'fields' => $this->fields,
            'mainLocation' => $this->getMainLocation(),
            'innerContent' => $this->getInnerContent(),
            'versionInfo',
            'innerVersionInfo' => $this->innerVersionInfo,
            'contentInfo' => $this->getContentInfo(),
            'owner' => $this->getOwner(),
            'innerOwnerUser' => $this->getInnerOwnerUser(),
            'modifier' => $this->getModifier(),
            'innerModifierUser' => $this->getInnerModifierUser(),
            'isVisible' => $this->getContentInfo()->isVisible,
            'url' => $this->internalGetUrl(),
            'path' => $this->internalGetPath(),
            default => parent::__get($property),
        };
    }

    /**
     * Magic isset for signaling existence of convenience properties.
     *
     * @param string $property
     */
    public function __isset($property): bool
    {
        return match ($property) {
            'contentInfo',
            'fields',
            'mainLocation',
            'innerContent',
            'versionInfo',
            'owner',
            'innerOwnerUser',
            'modifier',
            'innerModifierUser',
            'isVisible',
            'url' => true,
            default => parent::__isset($property),
        };
    }

    public function hasField(string $identifier): bool
    {
        return $this->fields->hasField($identifier);
    }

    public function getField(string $identifier): APIField
    {
        return $this->fields->getField($identifier);
    }

    public function hasFieldById($id): bool
    {
        return $this->fields->hasFieldById($id);
    }

    public function getFieldById($id): APIField
    {
        return $this->fields->getFieldById($id);
    }

    public function getFirstNonEmptyField(string $firstIdentifier, string ...$otherIdentifiers): APIField
    {
        return $this->fields->getFirstNonEmptyField($firstIdentifier, ...$otherIdentifiers);
    }

    public function getFieldValue(string $identifier): Value
    {
        return $this->getField($identifier)->value;
    }

    public function getFieldValueById($id): Value
    {
        return $this->getFieldById($id)->value;
    }

    public function getLocations(int $limit = 25): array
    {
        return $this->filterLocations($limit)->getIterator()->getArrayCopy();
    }

    public function filterLocations(int $maxPerPage = 25, int $currentPage = 1): Pagerfanta
    {
        $criteria = [
            new ContentId($this->id),
        ];

        if (!$this->site->getSettings()->showHiddenItems) {
            $criteria[] = new Visible(true);
        }

        $pager = new Pagerfanta(
            new FilterAdapter(
                new LocationQuery([
                    'filter' => new LogicalAnd($criteria),
                    'sortClauses' => [
                        new PathSortClause(),
                    ],
                ]),
                $this->site->getFilterService(),
            ),
        );

        $pager->setNormalizeOutOfRangePages(true);
        $pager->setMaxPerPage($maxPerPage);
        $pager->setCurrentPage($currentPage);

        return $pager;
    }

    public function getFieldRelation(string $fieldDefinitionIdentifier): ?APIContent
    {
        return $this->site->getRelationService()->loadFieldRelation(
            $this,
            $fieldDefinitionIdentifier,
        );
    }

    public function getSudoFieldRelation(string $fieldDefinitionIdentifier): ?APIContent
    {
        return $this->repository->sudo(
            fn () => $this->getFieldRelation($fieldDefinitionIdentifier),
        );
    }

    public function getFieldRelations(string $fieldDefinitionIdentifier, int $limit = 25): array
    {
        return $this->site->getRelationService()->loadFieldRelations(
            $this,
            $fieldDefinitionIdentifier,
            [],
            $limit,
        );
    }

    public function getSudoFieldRelations(string $fieldDefinitionIdentifier, int $limit = 25): array
    {
        return $this->repository->sudo(
            fn () => $this->getFieldRelations($fieldDefinitionIdentifier, $limit),
        );
    }

    public function filterFieldRelations(
        string $fieldDefinitionIdentifier,
        array $contentTypeIdentifiers = [],
        int $maxPerPage = 25,
        int $currentPage = 1,
    ): Pagerfanta {
        $relations = $this->site->getRelationService()->loadFieldRelations(
            $this,
            $fieldDefinitionIdentifier,
            $contentTypeIdentifiers,
        );

        $pager = new Pagerfanta(new ArrayAdapter($relations));

        $pager->setNormalizeOutOfRangePages(true);
        $pager->setMaxPerPage($maxPerPage);
        $pager->setCurrentPage($currentPage);

        return $pager;
    }

    public function filterSudoFieldRelations(
        string $fieldDefinitionIdentifier,
        array $contentTypeIdentifiers = [],
        int $maxPerPage = 25,
        int $currentPage = 1,
    ): Pagerfanta {
        return $this->repository->sudo(
            fn () => $this->filterFieldRelations(
                $fieldDefinitionIdentifier,
                $contentTypeIdentifiers,
                $maxPerPage,
                $currentPage,
            ),
        );
    }

    public function getFieldRelationLocation(string $fieldDefinitionIdentifier): ?APILocation
    {
        return $this->site->getRelationService()->loadFieldRelationLocation(
            $this,
            $fieldDefinitionIdentifier,
        );
    }

    public function getSudoFieldRelationLocation(string $fieldDefinitionIdentifier): ?APILocation
    {
        return $this->repository->sudo(
            fn () => $this->getFieldRelationLocation($fieldDefinitionIdentifier),
        );
    }

    public function getFieldRelationLocations(string $fieldDefinitionIdentifier, int $limit = 25): array
    {
        return $this->site->getRelationService()->loadFieldRelationLocations(
            $this,
            $fieldDefinitionIdentifier,
            [],
            $limit,
        );
    }

    public function getSudoFieldRelationLocations(string $fieldDefinitionIdentifier, int $limit = 25): array
    {
        return $this->repository->sudo(
            fn () => $this->getFieldRelationLocations($fieldDefinitionIdentifier, $limit),
        );
    }

    public function filterFieldRelationLocations(
        string $fieldDefinitionIdentifier,
        array $contentTypeIdentifiers = [],
        int $maxPerPage = 25,
        int $currentPage = 1,
    ): Pagerfanta {
        $relations = $this->site->getRelationService()->loadFieldRelationLocations(
            $this,
            $fieldDefinitionIdentifier,
            $contentTypeIdentifiers,
        );

        $pager = new Pagerfanta(new ArrayAdapter($relations));

        $pager->setNormalizeOutOfRangePages(true);
        $pager->setMaxPerPage($maxPerPage);
        $pager->setCurrentPage($currentPage);

        return $pager;
    }

    public function filterSudoFieldRelationLocations(
        string $fieldDefinitionIdentifier,
        array $contentTypeIdentifiers = [],
        int $maxPerPage = 25,
        int $currentPage = 1,
    ): Pagerfanta {
        return $this->repository->sudo(
            fn () => $this->filterFieldRelationLocations(
                $fieldDefinitionIdentifier,
                $contentTypeIdentifiers,
                $maxPerPage,
                $currentPage,
            ),
        );
    }

    private function getMainLocation(): ?APILocation
    {
        if ($this->internalMainLocation === null && $this->mainLocationId !== null) {
            $this->internalMainLocation = $this->site->getLoadService()->loadLocation(
                $this->mainLocationId,
            );
        }

        return $this->internalMainLocation;
    }

    private function getInnerContent(): RepoContent
    {
        if ($this->innerContent === null) {
            $this->innerContent = $this->repository->sudo(
                function (): RepoContent {
                    return $this->contentService->loadContent(
                        $this->id,
                        [$this->languageCode],
                        $this->innerVersionInfo->versionNo,
                    );
                },
            );
        }

        return $this->innerContent;
    }

    private function getContentInfo(): APIContentInfo
    {
        if ($this->contentInfo === null) {
            $this->contentInfo = $this->domainObjectMapper->mapContentInfo(
                $this->innerVersionInfo,
                $this->languageCode,
            );
        }

        return $this->contentInfo;
    }

    public function getPath(array $parameters = []): string
    {
        return $this->internalGetPath()->getAbsolute($parameters);
    }

    public function getUrl(array $parameters = []): string
    {
        return $this->internalGetUrl()->get($parameters);
    }

    private function getOwner(): ?APIContent
    {
        if ($this->isOwnerInitialized) {
            return $this->owner;
        }

        $this->owner = $this->repository->sudo(
            function (): ?APIContent {
                try {
                    return $this->site->getLoadService()->loadContent($this->getContentInfo()->ownerId);
                } catch (NotFoundException) {
                    // Do nothing
                }

                return null;
            },
        );

        $this->isOwnerInitialized = true;

        return $this->owner;
    }

    private function getInnerOwnerUser(): ?User
    {
        if ($this->isInnerOwnerUserInitialized) {
            return $this->innerOwnerUser;
        }

        try {
            $this->innerOwnerUser = $this->userService->loadUser($this->getContentInfo()->ownerId);
        } catch (NotFoundException) {
            // Do nothing
        }

        $this->isInnerOwnerUserInitialized = true;

        return $this->innerOwnerUser;
    }

    private function getModifier(): ?APIContent
    {
        if ($this->isModifierInitialized) {
            return $this->modifier;
        }

        $this->modifier = $this->repository->sudo(
            function (): ?APIContent {
                try {
                    return $this->site->getLoadService()->loadContent($this->innerVersionInfo->creatorId);
                } catch (NotFoundException) {
                    // Do nothing
                }

                return null;
            },
        );

        $this->isModifierInitialized = true;

        return $this->modifier;
    }

    private function getInnerModifierUser(): ?User
    {
        if ($this->isInnerModifierUserInitialized) {
            return $this->innerModifierUser;
        }

        try {
            $this->innerModifierUser = $this->userService->loadUser($this->innerVersionInfo->creatorId);
        } catch (NotFoundException) {
            // Do nothing
        }

        $this->isInnerModifierUserInitialized = true;

        return $this->innerModifierUser;
    }

    private function internalGetPath(): Path
    {
        if ($this->path === null) {
            $this->path = $this->domainObjectMapper->mapPath($this);
        }

        return $this->path;
    }

    private function internalGetUrl(): Url
    {
        if ($this->url === null) {
            $this->url = $this->domainObjectMapper->mapUrl($this);
        }

        return $this->url;
    }
}
