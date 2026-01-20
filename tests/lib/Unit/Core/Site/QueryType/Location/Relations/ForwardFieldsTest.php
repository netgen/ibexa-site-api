<?php

declare(strict_types=1);

namespace Netgen\IbexaSiteApi\Tests\Unit\Core\Site\QueryType\Location\Relations;

use Ibexa\Contracts\Core\Repository\Values\Content\Field as RepoField;
use Ibexa\Contracts\Core\Repository\Values\Content\LocationQuery;
use Ibexa\Contracts\Core\Repository\Values\Content\Query;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion\ContentId;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion\ContentTypeIdentifier;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion\DateMetadata;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion\Field;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion\Location\IsMainLocation;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion\LogicalAnd;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion\MatchNone;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion\Operator;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\SortClause\DatePublished;
use Ibexa\Core\FieldType\Relation\Value as RelationValue;
use Ibexa\Core\FieldType\RelationList\Value as RelationListValue;
use Ibexa\Core\FieldType\TextLine\Value;
use Ibexa\Core\Repository\Values\ContentType\FieldDefinition;
use Ibexa\Core\Repository\Values\ContentType\FieldDefinitionCollection;
use Netgen\IbexaSearchExtra\API\Values\Content\Query\Criterion\Visible;
use Netgen\IbexaSearchExtra\API\Values\Content\Query\SortClause\ContentName;
use Netgen\IbexaSiteApi\Core\Site\Plugins\FieldType\RelationResolver\Registry;
use Netgen\IbexaSiteApi\Core\Site\Plugins\FieldType\RelationResolver\Resolver\Relation;
use Netgen\IbexaSiteApi\Core\Site\Plugins\FieldType\RelationResolver\Resolver\RelationList;
use Netgen\IbexaSiteApi\Core\Site\Plugins\FieldType\RelationResolver\Resolver\Surrogate;
use Netgen\IbexaSiteApi\Core\Site\QueryType\Location\Relations\ForwardFields;
use Netgen\IbexaSiteApi\Core\Site\QueryType\QueryType;
use Netgen\IbexaSiteApi\Core\Site\Settings;
use Netgen\IbexaSiteApi\Core\Site\Values\Content;
use Netgen\IbexaSiteApi\Tests\Unit\Core\Site\ContentFieldsMockTrait;
use Netgen\IbexaSiteApi\Tests\Unit\Core\Site\QueryType\QueryTypeBaseTestCase;
use OutOfBoundsException;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\Attributes\Group;
use Psr\Log\NullLogger;
use RuntimeException;

/**
 * ForwardFields Location Relation QueryType test case.
 *
 * @see \Netgen\IbexaSiteApi\Core\Site\QueryType\Location\Relations\ForwardFields
 *
 * @internal
 */
#[Group('query-type')]
#[AllowMockObjectsWithoutExpectations]
final class ForwardFieldsTest extends QueryTypeBaseTestCase
{
    private const string EXPECT_TEST_CONTENT = '__content__';
    private const string EXPECT_TEST_CONTENT_NO_FIELDS_FAIL = '__content_no_fields_fail__';

    use ContentFieldsMockTrait;

    public static function provideGetQueryCases(): array
    {
        return [
            [
                false,
                [
                    'content' => self::EXPECT_TEST_CONTENT,
                    'relation_field' => ['relations_a', 'relations_b'],
                    'limit' => 12,
                    'offset' => 34,
                    'sort' => 'published asc',
                ],
                new LocationQuery([
                    'filter' => new LogicalAnd([
                        new Visible(true),
                        new IsMainLocation(IsMainLocation::MAIN),
                        new ContentId([1, 2, 3, 4]),
                    ]),
                    'limit' => 12,
                    'offset' => 34,
                    'sortClauses' => [
                        new DatePublished(Query::SORT_ASC),
                    ],
                ]),
            ],
            [
                false,
                [
                    'content' => self::EXPECT_TEST_CONTENT,
                    'relation_field' => ['relations_a'],
                    'content_type' => 'article',
                    'field' => [],
                    'sort' => [
                        'published asc',
                    ],
                ],
                new LocationQuery([
                    'filter' => new LogicalAnd([
                        new Visible(true),
                        new IsMainLocation(IsMainLocation::MAIN),
                        new ContentTypeIdentifier('article'),
                        new ContentId([1, 2, 3]),
                    ]),
                    'sortClauses' => [
                        new DatePublished(Query::SORT_ASC),
                    ],
                ]),
            ],
            [
                false,
                [
                    'content' => self::EXPECT_TEST_CONTENT,
                    'relation_field' => ['relations_b'],
                    'content_type' => 'article',
                    'field' => [
                        'title' => 'Hello',
                    ],
                    'sort' => [
                        'published desc',
                        'name asc',
                    ],
                ],
                new LocationQuery([
                    'filter' => new LogicalAnd([
                        new Visible(true),
                        new IsMainLocation(IsMainLocation::MAIN),
                        new ContentTypeIdentifier('article'),
                        new Field('title', Operator::EQ, 'Hello'),
                        new ContentId([4]),
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
                    'content' => self::EXPECT_TEST_CONTENT,
                    'relation_field' => [],
                    'content_type' => 'article',
                    'field' => [
                        'title' => [
                            'eq' => 'Hello',
                        ],
                    ],
                    'sort' => new DatePublished(Query::SORT_DESC),
                ],
                new LocationQuery([
                    'filter' => new LogicalAnd([
                        new Visible(true),
                        new IsMainLocation(IsMainLocation::MAIN),
                        new ContentTypeIdentifier('article'),
                        new Field('title', Operator::EQ, 'Hello'),
                        new MatchNone(),
                    ]),
                    'sortClauses' => [
                        new DatePublished(Query::SORT_DESC),
                    ],
                ]),
            ],
            [
                false,
                [
                    'content' => self::EXPECT_TEST_CONTENT,
                    'relation_field' => ['relations_a', 'relations_b'],
                    'content_type' => 'article',
                    'field' => [
                        'title' => [
                            'eq' => 'Hello',
                            'gte' => 7,
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
                        new IsMainLocation(IsMainLocation::MAIN),
                        new ContentTypeIdentifier('article'),
                        new Field('title', Operator::EQ, 'Hello'),
                        new Field('title', Operator::GTE, 7),
                        new ContentId([1, 2, 3, 4]),
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
                    'content' => self::EXPECT_TEST_CONTENT,
                    'relation_field' => ['relations_a', 'relations_b'],
                    'creation_date' => '4 May 2018',
                    'sort' => [
                        new DatePublished(Query::SORT_DESC),
                        new ContentName(Query::SORT_ASC),
                    ],
                ],
                new LocationQuery([
                    'filter' => new LogicalAnd([
                        new Visible(true),
                        new IsMainLocation(IsMainLocation::MAIN),
                        new DateMetadata(
                            DateMetadata::CREATED,
                            Operator::EQ,
                            1525384800,
                        ),
                        new ContentId([1, 2, 3, 4]),
                    ]),
                    'sortClauses' => [
                        new DatePublished(Query::SORT_DESC),
                        new ContentName(Query::SORT_ASC),
                    ],
                ]),
            ],
        ];
    }

    public function testGetQueryWithUnsupportedField(): void
    {
        $this->expectException(OutOfBoundsException::class);

        $queryType = $this->getQueryTypeUnderTest();
        $content = $this->getTestContent();

        $queryType->getQuery([
            'content' => $content,
            'relation_field' => ['not_relations'],
            'content_type' => 'article',
            'sort' => 'published desc',
        ]);
    }

    public function testGetQueryWithNonexistentFieldFails(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Field "relations_c" in Content #42 does not exist');

        $queryType = $this->getQueryTypeUnderTest();
        $content = $this->getTestContent();

        $queryType->getQuery([
            'content' => $content,
            'relation_field' => ['relations_a', 'relations_c'],
            'content_type' => 'article',
            'sort' => 'published desc',
        ]);
    }

    public function testGetQueryWithNonexistentFieldDoesNotFail(): void
    {
        $queryType = $this->getQueryTypeUnderTest();
        $content = $this->getTestContent(false);

        $query = $queryType->getQuery([
            'content' => $content,
            'relation_field' => ['relations_a', 'relations_c'],
            'content_type' => 'article',
            'sort' => 'published desc',
        ]);

        self::assertEquals(
            new LocationQuery([
                'filter' => new LogicalAnd([
                    new Visible(true),
                    new IsMainLocation(IsMainLocation::MAIN),
                    new ContentTypeIdentifier('article'),
                    new ContentId([1, 2, 3]),
                ]),
                'sortClauses' => [
                    new DatePublished(Query::SORT_DESC),
                ],
            ]),
            $query,
        );
    }

    public static function provideGetQueryWithInvalidOptionsCases(): array
    {
        return [
            [
                [
                    'content' => self::EXPECT_TEST_CONTENT,
                    'relation_field' => 'field',
                    'content_type' => 1,
                ],
            ],
            [
                [
                    'content' => self::EXPECT_TEST_CONTENT,
                    'relation_field' => 'field',
                    'field' => 1,
                ],
            ],
            [
                [
                    'content' => self::EXPECT_TEST_CONTENT,
                    'relation_field' => 'field',
                    'creation_date' => true,
                ],
            ],
            [
                [
                    'content' => self::EXPECT_TEST_CONTENT,
                    'relation_field' => 'field',
                    'limit' => 'five',
                ],
            ],
            [
                [
                    'content' => self::EXPECT_TEST_CONTENT,
                    'relation_field' => 'field',
                    'offset' => 'ten',
                ],
            ],
            [
                [
                    'content' => self::EXPECT_TEST_CONTENT,
                    'relation_field' => [1],
                ],
            ],
        ];
    }

    public static function provideGetQueryWithInvalidCriteriaCases(): array
    {
        return [
            [
                [
                    'content' => self::EXPECT_TEST_CONTENT,
                    'relation_field' => ['relations_a', 'relations_b'],
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
                    'content' => self::EXPECT_TEST_CONTENT,
                    'relation_field' => ['relations_a', 'relations_b'],
                    'sort' => 'just sort it',
                ],
            ],
        ];
    }

    protected function getQueryTypeName(): string
    {
        return 'SiteAPI:Location/Relations/ForwardFields';
    }

    protected function getQueryTypeUnderTest(bool $showHiddenItems = false): QueryType
    {
        return new ForwardFields(
            new Settings(
                ['eng-GB'],
                true,
                2,
                $showHiddenItems,
                true,
            ),
            new Registry([
                'ezobjectrelation' => new Relation(),
                'ezobjectrelationlist' => new RelationList(),
                'ngsurrogate' => new Surrogate(),
            ]),
        );
    }

    protected function internalGetRepoFields(): array
    {
        return [
            new RepoField([
                'id' => 1,
                'fieldDefIdentifier' => 'relations_a',
                'value' => new RelationListValue([1, 2, 3]),
                'languageCode' => 'eng-GB',
                'fieldTypeIdentifier' => 'ezobjectrelationlist',
            ]),
            new RepoField([
                'id' => 2,
                'fieldDefIdentifier' => 'relations_b',
                'value' => new RelationValue(4),
                'languageCode' => 'eng-GB',
                'fieldTypeIdentifier' => 'ezobjectrelation',
            ]),
            new RepoField([
                'id' => 3,
                'fieldDefIdentifier' => 'not_relations',
                'value' => new Value(),
                'languageCode' => 'eng-GB',
                'fieldTypeIdentifier' => 'ezstring',
            ]),
        ];
    }

    protected function internalGetRepoFieldDefinitions(): FieldDefinitionCollection
    {
        return new FieldDefinitionCollection([
            new FieldDefinition([
                'id' => 1,
                'identifier' => 'relations_a',
                'fieldTypeIdentifier' => 'ezobjectrelationlist',
            ]),
            new FieldDefinition([
                'id' => 2,
                'identifier' => 'relations_b',
                'fieldTypeIdentifier' => 'ezobjectrelation',
            ]),
            new FieldDefinition([
                'id' => 3,
                'identifier' => 'not_relations',
                'fieldTypeIdentifier' => 'ezstring',
            ]),
        ]);
    }

    protected function getTestContent(bool $failOnMissingFields = true): Content
    {
        return new Content(
            [
                'id' => 42,
                'site' => $this->getSiteMock(),
                'name' => 'Krešo',
                'mainLocationId' => 123,
                'domainObjectMapper' => $this->getDomainObjectMapper($failOnMissingFields),
                'repository' => $this->getRepositoryMock(),
                'innerVersionInfo' => $this->getRepoVersionInfo(),
                'languageCode' => 'eng-GB',
            ],
            $failOnMissingFields,
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
            'depth',
            'main',
            'parent_location_id',
            'priority',
            'subtree',
            'content',
            'relation_field',
        ];
    }

    protected function resolveExpectedMock(mixed $value): mixed
    {
        return match ($value) {
            self::EXPECT_TEST_CONTENT => $this->getTestContent(),
            self::EXPECT_TEST_CONTENT_NO_FIELDS_FAIL => $this->getTestContent(false),
            default => $value,
        };
    }
}
