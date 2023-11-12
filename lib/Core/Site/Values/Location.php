<?php

declare(strict_types=1);

namespace Netgen\IbexaSiteApi\Core\Site\Values;

use Ibexa\Contracts\Core\Repository\Exceptions\NotImplementedException;
use Ibexa\Contracts\Core\Repository\Values\Content\Location as RepoLocation;
use Ibexa\Contracts\Core\Repository\Values\Content\LocationQuery;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion\ContentTypeIdentifier;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion\LocationId;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion\LogicalAnd;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion\LogicalNot;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion\ParentLocationId;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\SortClause\ContentName as CoreContentName;
use Ibexa\Contracts\Core\Repository\Values\Content\VersionInfo;
use Netgen\IbexaSearchExtra\API\Values\Content\Query\Criterion\Visible;
use Netgen\IbexaSearchExtra\API\Values\Content\Query\SortClause\ContentName as SearchExtraContentName;
use Netgen\IbexaSiteApi\API\Site;
use Netgen\IbexaSiteApi\API\Values\Content as APIContent;
use Netgen\IbexaSiteApi\API\Values\ContentInfo as APIContentInfo;
use Netgen\IbexaSiteApi\API\Values\Location as APILocation;
use Netgen\IbexaSiteApi\Core\Site\DomainObjectMapper;
use Netgen\IbexaSiteApi\Core\Site\Pagination\Pagerfanta\FilterAdapter;
use Pagerfanta\Pagerfanta;
use Psr\Log\LoggerInterface;

use function property_exists;
use function sprintf;

final class Location extends APILocation
{
    protected RepoLocation $innerLocation;
    private readonly string $languageCode;
    private ?APIContentInfo $contentInfo = null;
    private ?APILocation $internalParent = null;
    private ?APIContent $internalContent = null;
    private readonly VersionInfo $innerVersionInfo;
    private readonly Site $site;
    private readonly DomainObjectMapper $domainObjectMapper;

    public function __construct(
        array $properties,
        private readonly LoggerInterface $logger,
    ) {
        $this->site = $properties['site'];
        $this->domainObjectMapper = $properties['domainObjectMapper'];
        $this->innerVersionInfo = $properties['innerVersionInfo'];
        $this->innerLocation = $properties['innerLocation'];
        $this->languageCode = $properties['languageCode'];

        unset(
            $properties['site'],
            $properties['domainObjectMapper'],
            $properties['innerVersionInfo'],
            $properties['innerLocation'],
            $properties['languageCode']
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
        switch ($property) {
            case 'contentId':
                return $this->innerLocation->contentId;

            case 'parent':
                return $this->getParent();

            case 'content':
                return $this->getContent();

            case 'contentInfo':
                return $this->getContentInfo();

            case 'isVisible':
                return !$this->innerLocation->hidden && !$this->innerLocation->invisible;
        }

        if (property_exists($this, $property)) {
            return $this->{$property};
        }

        if (property_exists($this->innerLocation, $property)) {
            return $this->innerLocation->{$property};
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
            case 'contentId':
            case 'parent':
            case 'content':
            case 'isVisible':
                return true;
        }

        if (property_exists($this, $property) || property_exists($this->innerLocation, $property)) {
            return true;
        }

        return parent::__isset($property);
    }

    public function getChildren(int $limit = 25): array
    {
        return $this->filterChildren([], $limit)->getIterator()->getArrayCopy();
    }

    public function filterChildren(array $contentTypeIdentifiers = [], int $maxPerPage = 25, int $currentPage = 1): Pagerfanta
    {
        $criteria = [
            new ParentLocationId($this->id),
        ];

        if (!$this->site->getSettings()->showHiddenItems) {
            $criteria[] = new Visible(true);
        }

        if (!empty($contentTypeIdentifiers)) {
            $criteria[] = new ContentTypeIdentifier($contentTypeIdentifiers);
        }

        return $this->getFilterPager($criteria, $maxPerPage, $currentPage);
    }

    public function getFirstChild(?string $contentTypeIdentifier = null): ?APILocation
    {
        $contentTypeIdentifiers = [];

        if ($contentTypeIdentifier !== null) {
            $contentTypeIdentifiers = [$contentTypeIdentifier];
        }

        $pager = $this->filterChildren($contentTypeIdentifiers, 1);

        if ($pager->count() > 0) {
            return $pager->getIterator()->current();
        }

        return null;
    }

    public function getSiblings(int $limit = 25): array
    {
        return $this->filterSiblings([], $limit)->getIterator()->getArrayCopy();
    }

    public function filterSiblings(array $contentTypeIdentifiers = [], int $maxPerPage = 25, int $currentPage = 1): Pagerfanta
    {
        $criteria = [
            new ParentLocationId($this->parentLocationId),
            new LogicalNot(
                new LocationId($this->id),
            ),
        ];

        if (!$this->site->getSettings()->showHiddenItems) {
            $criteria[] = new Visible(true);
        }

        if (!empty($contentTypeIdentifiers)) {
            $criteria[] = new ContentTypeIdentifier($contentTypeIdentifiers);
        }

        return $this->getFilterPager($criteria, $maxPerPage, $currentPage);
    }

    public function getSortClauses(): array
    {
        $sortClauses = [];
        $coreSortClauses = $this->innerLocation->getSortClauses();

        foreach ($coreSortClauses as $coreSortClause) {
            if ($coreSortClause instanceof CoreContentName) {
                $sortClauses[] = new SearchExtraContentName();

                continue;
            }

            $sortClauses[] = $coreSortClause;
        }

        return $sortClauses;
    }

    private function getFilterPager(array $criteria, int $maxPerPage = 25, int $currentPage = 1): Pagerfanta
    {
        try {
            $sortClauses = $this->innerLocation->getSortClauses();
        } catch (NotImplementedException $e) {
            $this->logger->notice(
                sprintf(
                    'Cannot use sort clauses from parent location: %s',
                    $e->getMessage(),
                ),
            );

            $sortClauses = [];
        }

        $pager = new Pagerfanta(
            new FilterAdapter(
                new LocationQuery([
                    'filter' => new LogicalAnd($criteria),
                    'sortClauses' => $sortClauses,
                ]),
                $this->site->getFilterService(),
            ),
        );

        $pager->setNormalizeOutOfRangePages(true);
        $pager->setMaxPerPage($maxPerPage);
        $pager->setCurrentPage($currentPage);

        return $pager;
    }

    private function getParent(): APILocation
    {
        if ($this->internalParent === null) {
            $this->internalParent = $this->site->getLoadService()->loadLocation(
                $this->parentLocationId,
            );
        }

        return $this->internalParent;
    }

    private function getContent(): APIContent
    {
        if ($this->internalContent === null) {
            $this->internalContent = $this->domainObjectMapper->mapContent(
                $this->innerVersionInfo,
                $this->languageCode,
            );
        }

        return $this->internalContent;
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
}
