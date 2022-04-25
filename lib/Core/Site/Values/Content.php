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
use Ibexa\Contracts\Core\Repository\Values\Content\Query\SortClause\Location\Path;
use Ibexa\Contracts\Core\Repository\Values\Content\VersionInfo;
use Ibexa\Contracts\Core\Repository\Values\User\User;
use Netgen\IbexaSearchExtra\API\Values\Content\Query\Criterion\Visible;
use Netgen\IbexaSiteApi\API\Site;
use Netgen\IbexaSiteApi\API\Values\Content as APIContent;
use Netgen\IbexaSiteApi\API\Values\ContentInfo as APIContentInfo;
use Netgen\IbexaSiteApi\API\Values\Field as APIField;
use Netgen\IbexaSiteApi\API\Values\Location as APILocation;
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
    protected string $name;
    protected string $languageCode;
    protected ?int $mainLocationId;

    private ?APIContent $owner = null;
    private ?User $innerOwnerUser = null;
    private ?APIContent $modifier = null;
    private ?User $innerModifierUser = null;
    private ?APIContentInfo $contentInfo = null;
    private ?RepoContent $innerContent = null;
    private ?APILocation $internalMainLocation = null;

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
     *
     * @throws \Exception
     */
    public function __get($property)
    {
        switch ($property) {
            case 'fields':
                return $this->fields;

            case 'mainLocation':
                return $this->getMainLocation();

            case 'innerContent':
                return $this->getInnerContent();

            case 'versionInfo':
            case 'innerVersionInfo':
                return $this->innerVersionInfo;

            case 'contentInfo':
                return $this->getContentInfo();

            case 'owner':
                return $this->getOwner();

            case 'innerOwnerUser':
                return $this->getInnerOwnerUser();

            case 'modifier':
                return $this->getModifier();

            case 'innerModifierUser':
                return $this->getInnerModifierUser();

            case 'isVisible':
                return $this->getContentInfo()->isVisible;
        }

        return parent::__get($property);
    }

    /**
     * Magic isset for signaling existence of convenience properties.
     *
     * @param string $property
     */
    public function __isset($property): bool
    {
        switch ($property) {
            case 'contentInfo':
            case 'fields':
            case 'mainLocation':
            case 'innerContent':
            case 'versionInfo':
            case 'owner':
            case 'innerOwnerUser':
            case 'modifier':
            case 'innerModifierUser':
            case 'isVisible':
                return true;
        }

        return parent::__isset($property);
    }

    /**
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException
     */
    public function __debugInfo(): array
    {
        return [
            'id' => $this->id,
            'mainLocationId' => $this->mainLocationId,
            'name' => $this->name,
            'languageCode' => $this->languageCode,
            'isVisible' => $this->getContentInfo()->isVisible,
            'contentInfo' => $this->getContentInfo(),
            'fields' => $this->fields,
            'mainLocation' => '[An instance of Netgen\IbexaSiteApi\API\Values\Location]',
            'innerContent' => '[An instance of Ibexa\Contracts\Core\Repository\Values\Content\Content]',
            'innerVersionInfo' => '[An instance of Ibexa\Contracts\Core\Repository\Values\Content\VersionInfo]',
        ];
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException
     */
    public function hasField(string $identifier): bool
    {
        return $this->fields->hasField($identifier);
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException
     */
    public function getField(string $identifier): APIField
    {
        return $this->fields->getField($identifier);
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException
     */
    public function hasFieldById($id): bool
    {
        return $this->fields->hasFieldById($id);
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException
     */
    public function getFieldById($id): APIField
    {
        return $this->fields->getFieldById($id);
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException
     */
    public function getFirstNonEmptyField(string $firstIdentifier, string ...$otherIdentifiers): APIField
    {
        return $this->fields->getFirstNonEmptyField($firstIdentifier, ...$otherIdentifiers);
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException
     */
    public function getFieldValue(string $identifier): Value
    {
        return $this->getField($identifier)->value;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException
     */
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
                        new Path(),
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

    public function getFieldRelations(string $fieldDefinitionIdentifier, int $limit = 25): array
    {
        return $this->site->getRelationService()->loadFieldRelations(
            $this,
            $fieldDefinitionIdentifier,
            [],
            $limit,
        );
    }

    public function filterFieldRelations(
        string $fieldDefinitionIdentifier,
        array $contentTypeIdentifiers = [],
        int $maxPerPage = 25,
        int $currentPage = 1
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

    public function getFieldRelationLocation(string $fieldDefinitionIdentifier): ?APILocation
    {
        return $this->site->getRelationService()->loadFieldRelationLocation(
            $this,
            $fieldDefinitionIdentifier,
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

    public function filterFieldRelationLocations(
        string $fieldDefinitionIdentifier,
        array $contentTypeIdentifiers = [],
        int $maxPerPage = 25,
        int $currentPage = 1
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

    /**
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException
     * @throws \Netgen\IbexaSiteApi\API\Exceptions\TranslationNotMatchedException
     */
    private function getMainLocation(): ?APILocation
    {
        if ($this->internalMainLocation === null && $this->mainLocationId !== null) {
            $this->internalMainLocation = $this->site->getLoadService()->loadLocation(
                $this->mainLocationId,
            );
        }

        return $this->internalMainLocation;
    }

    /**
     * @throws \Exception
     */
    private function getInnerContent(): RepoContent
    {
        if ($this->innerContent === null) {
            $this->innerContent = $this->repository->sudo(
                function (Repository $repository): RepoContent {
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

    /**
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException
     */
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

    /**
     * @throws \Exception
     */
    private function getOwner(): ?APIContent
    {
        if ($this->isOwnerInitialized) {
            return $this->owner;
        }

        $this->owner = $this->repository->sudo(
            function (Repository $repository): ?APIContent {
                try {
                    return $this->site->getLoadService()->loadContent($this->getContentInfo()->ownerId);
                } catch (NotFoundException $e) {
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
        } catch (NotFoundException $e) {
            // Do nothing
        }

        $this->isInnerOwnerUserInitialized = true;

        return $this->innerOwnerUser;
    }

    /**
     * @throws \Exception
     */
    private function getModifier(): ?APIContent
    {
        if ($this->isModifierInitialized) {
            return $this->modifier;
        }

        $this->modifier = $this->repository->sudo(
            function (Repository $repository): ?APIContent {
                try {
                    return $this->site->getLoadService()->loadContent($this->innerVersionInfo->creatorId);
                } catch (NotFoundException $e) {
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
        } catch (NotFoundException $e) {
            // Do nothing
        }

        $this->isInnerModifierUserInitialized = true;

        return $this->innerModifierUser;
    }
}
