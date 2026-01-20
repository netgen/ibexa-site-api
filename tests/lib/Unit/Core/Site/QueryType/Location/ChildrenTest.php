<?php

declare(strict_types=1);

namespace Netgen\IbexaSiteApi\Tests\Unit\Core\Site\QueryType\Location;

use Ibexa\Contracts\Core\Repository\Values\Content\Location as APILocation;
use Ibexa\Contracts\Core\Repository\Values\Content\LocationQuery;
use Ibexa\Contracts\Core\Repository\Values\Content\Query;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion\ContentTypeIdentifier;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion\DateMetadata;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion\Field;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion\LogicalAnd;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion\Operator;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion\ParentLocationId;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\SortClause\DatePublished;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\SortClause\Location\Priority;
use Ibexa\Core\Repository\Values\Content\Location as RepositoryLocation;
use Ibexa\Core\Repository\Values\ContentType\FieldDefinitionCollection;
use Netgen\IbexaSearchExtra\API\Values\Content\Query\Criterion\Visible;
use Netgen\IbexaSearchExtra\API\Values\Content\Query\SortClause\ContentName;
use Netgen\IbexaSiteApi\API\Site;
use Netgen\IbexaSiteApi\Core\Site\QueryType\Location\Children;
use Netgen\IbexaSiteApi\Core\Site\QueryType\QueryType;
use Netgen\IbexaSiteApi\Core\Site\Settings;
use Netgen\IbexaSiteApi\Core\Site\Values\Location;
use Netgen\IbexaSiteApi\Tests\Unit\Core\Site\ContentFieldsMockTrait;
use Netgen\IbexaSiteApi\Tests\Unit\Core\Site\QueryType\QueryTypeBaseTestCase;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\NullLogger;

/**
 * Location Children QueryType test case.
 *
 * @internal
 */
#[Group('query-type')]
#[AllowMockObjectsWithoutExpectations]
final class ChildrenTest extends QueryTypeBaseTestCase
{
    private const string EXPECT_TEST_LOCATION = '__location__';

    use ContentFieldsMockTrait;

    public static function provideGetQueryCases(): array
    {
        return [
            [
                false,
                [
                    'location' => self::EXPECT_TEST_LOCATION,
                ],
                new LocationQuery([
                    'filter' => new LogicalAnd([
                        new Visible(true),
                        new ParentLocationId(42),
                    ]),
                    'sortClauses' => [
                        new Priority(Query::SORT_DESC),
                    ],
                ]),
            ],
            [
                false,
                [
                    'visible' => false,
                    'location' => self::EXPECT_TEST_LOCATION,
                    'sort' => 'published asc',
                ],
                new LocationQuery([
                    'filter' => new LogicalAnd([
                        new Visible(false),
                        new ParentLocationId(42),
                    ]),
                    'sortClauses' => [
                        new DatePublished(Query::SORT_ASC),
                    ],
                ]),
            ],
            [
                false,
                [
                    'visible' => null,
                    'location' => self::EXPECT_TEST_LOCATION,
                    'limit' => 12,
                    'offset' => 34,
                    'sort' => 'published desc',
                ],
                new LocationQuery([
                    'filter' => new ParentLocationId(42),
                    'limit' => 12,
                    'offset' => 34,
                    'sortClauses' => [
                        new DatePublished(Query::SORT_DESC),
                    ],
                ]),
            ],
            [
                true,
                [
                    'location' => self::EXPECT_TEST_LOCATION,
                    'content_type' => 'article',
                    'sort' => [
                        'published asc',
                    ],
                ],
                new LocationQuery([
                    'filter' => new LogicalAnd([
                        new ContentTypeIdentifier('article'),
                        new ParentLocationId(42),
                    ]),
                    'sortClauses' => [
                        new DatePublished(Query::SORT_ASC),
                    ],
                ]),
            ],
            [
                true,
                [
                    'visible' => true,
                    'location' => self::EXPECT_TEST_LOCATION,
                    'content_type' => 'article',
                    'field' => [],
                    'sort' => [
                        'published desc',
                        'name asc',
                    ],
                ],
                new LocationQuery([
                    'filter' => new LogicalAnd([
                        new Visible(true),
                        new ContentTypeIdentifier('article'),
                        new ParentLocationId(42),
                    ]),
                    'sortClauses' => [
                        new DatePublished(Query::SORT_DESC),
                        new ContentName(Query::SORT_ASC),
                    ],
                ]),
            ],
            [
                false,
                [
                    'location' => self::EXPECT_TEST_LOCATION,
                    'content_type' => 'article',
                    'field' => [
                        'title' => 'Hello',
                    ],
                    'sort' => new DatePublished(Query::SORT_DESC),
                ],
                new LocationQuery([
                    'filter' => new LogicalAnd([
                        new Visible(true),
                        new ContentTypeIdentifier('article'),
                        new Field('title', Operator::EQ, 'Hello'),
                        new ParentLocationId(42),
                    ]),
                    'sortClauses' => [
                        new DatePublished(Query::SORT_DESC),
                    ],
                ]),
            ],
            [
                false,
                [
                    'location' => self::EXPECT_TEST_LOCATION,
                    'content_type' => 'article',
                    'field' => [
                        'title' => [
                            'eq' => 'Hello',
                        ],
                    ],
                    'sort' => [
                        'published desc',
                        new ContentName(Query::SORT_ASC),
                    ],
                ],
                new LocationQuery([
                    'filter' => new LogicalAnd([
                        new Visible(true),
                        new ContentTypeIdentifier('article'),
                        new Field('title', Operator::EQ, 'Hello'),
                        new ParentLocationId(42),
                    ]),
                    'sortClauses' => [
                        new DatePublished(Query::SORT_DESC),
                        new ContentName(Query::SORT_ASC),
                    ],
                ]),
            ],
            [
                false,
                [
                    'location' => self::EXPECT_TEST_LOCATION,
                    'content_type' => 'article',
                    'field' => [
                        'title' => [
                            'eq' => 'Hello',
                            'gte' => 7,
                        ],
                    ],
                    'sort' => [
                        new DatePublished(Query::SORT_DESC),
                        new ContentName(Query::SORT_ASC),
                    ],
                ],
                new LocationQuery([
                    'filter' => new LogicalAnd([
                        new Visible(true),
                        new ContentTypeIdentifier('article'),
                        new Field('title', Operator::EQ, 'Hello'),
                        new Field('title', Operator::GTE, 7),
                        new ParentLocationId(42),
                    ]),
                    'sortClauses' => [
                        new DatePublished(Query::SORT_DESC),
                        new ContentName(Query::SORT_ASC),
                    ],
                ]),
            ],
            [
                false,
                [
                    'location' => self::EXPECT_TEST_LOCATION,
                    'creation_date' => '4 May 2018',
                ],
                new LocationQuery([
                    'filter' => new LogicalAnd([
                        new Visible(true),
                        new DateMetadata(
                            DateMetadata::CREATED,
                            Operator::EQ,
                            1525384800,
                        ),
                        new ParentLocationId(42),
                    ]),
                    'sortClauses' => [
                        new Priority(Query::SORT_DESC),
                    ],
                ]),
            ],
        ];
    }

    public static function provideGetQueryWithInvalidOptionsCases(): array
    {
        return [
            [
                [
                    'location' => self::EXPECT_TEST_LOCATION,
                    'content_type' => 1,
                ],
            ],
            [
                [
                    'location' => self::EXPECT_TEST_LOCATION,
                    'field' => 1,
                ],
            ],
            [
                [
                    'location' => self::EXPECT_TEST_LOCATION,
                    'creation_date' => true,
                ],
            ],
            [
                [
                    'location' => self::EXPECT_TEST_LOCATION,
                    'limit' => 'five',
                ],
            ],
            [
                [
                    'location' => self::EXPECT_TEST_LOCATION,
                    'offset' => 'ten',
                ],
            ],
        ];
    }

    public static function provideGetQueryWithInvalidCriteriaCases(): array
    {
        return [
            [
                [
                    'location' => self::EXPECT_TEST_LOCATION,
                    'creation_date' => [
                        'like' => 5,
                    ],
                ],
            ],
        ];
    }

    public static function provideInvalidSortClauseThrowsExceptionCases(): array
    {
        return [
            [
                [
                    'location' => self::EXPECT_TEST_LOCATION,
                    'sort' => 'just sort it',
                ],
            ],
        ];
    }

    public function internalGetRepoFields(): array
    {
        return [];
    }

    protected function getQueryTypeName(): string
    {
        return 'SiteAPI:Location/Children';
    }

    protected function getQueryTypeUnderTest(bool $showHiddenItems = false): QueryType
    {
        return new Children(
            new Settings(
                ['eng-GB'],
                true,
                2,
                $showHiddenItems,
                true,
            ),
            new NullLogger(),
        );
    }

    protected function getTestLocation(): Location
    {
        return new Location(
            [
                'site' => $this->getSiteMock(),
                'domainObjectMapper' => $this->getDomainObjectMapper(),
                'innerVersionInfo' => $this->getRepoVersionInfo(),
                'languageCode' => 'cro-HR',
                'innerLocation' => new RepositoryLocation([
                    'id' => 42,
                    'sortField' => APILocation::SORT_FIELD_PRIORITY,
                    'sortOrder' => APILocation::SORT_ORDER_DESC,
                    'contentInfo' => $this->getRepoContentInfo(),
                ]),
            ],
            new NullLogger(),
        );
    }

    protected function getSupportedParameters(): array
    {
        return [
            'content_type',
            'field',
            'is_field_empty',
            'creation_date',
            'modification_date',
            'section',
            'state',
            'visible',
            'sort',
            'limit',
            'offset',
            'main',
            'priority',
            'location',
        ];
    }

    protected function getSiteMock(): MockObject|Site
    {
        if ($this->siteMock !== null) {
            return $this->siteMock;
        }

        $this->siteMock = $this->getMockBuilder(Site::class)->getMock();

        return $this->siteMock;
    }

    protected function internalGetRepoFieldDefinitions(): FieldDefinitionCollection
    {
        return new FieldDefinitionCollection();
    }

    protected function resolveExpectedMock(mixed $value): mixed
    {
        return match ($value) {
            self::EXPECT_TEST_LOCATION => $this->getTestLocation(),
            default => $value,
        };
    }
}
