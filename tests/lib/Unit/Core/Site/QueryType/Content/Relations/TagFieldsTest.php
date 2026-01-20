<?php

declare(strict_types=1);

namespace Netgen\IbexaSiteApi\Tests\Unit\Core\Site\QueryType\Content\Relations;

use Ibexa\Contracts\Core\Repository\Values\Content\Field as RepoField;
use Ibexa\Contracts\Core\Repository\Values\Content\Query;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion\ContentId;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion\ContentTypeIdentifier;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion\DateMetadata;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion\Field;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion\LogicalAnd;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion\LogicalNot;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion\MatchNone;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion\Operator;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\SortClause\DatePublished;
use Ibexa\Core\FieldType\TextLine\Value;
use Ibexa\Core\Repository\Values\ContentType\FieldDefinition;
use Ibexa\Core\Repository\Values\ContentType\FieldDefinitionCollection;
use InvalidArgumentException;
use Netgen\IbexaSearchExtra\API\Values\Content\Query\Criterion\Visible;
use Netgen\IbexaSearchExtra\API\Values\Content\Query\SortClause\ContentName;
use Netgen\IbexaSiteApi\Core\Site\QueryType\Content\Relations\TagFields;
use Netgen\IbexaSiteApi\Core\Site\QueryType\QueryType;
use Netgen\IbexaSiteApi\Core\Site\Settings;
use Netgen\IbexaSiteApi\Core\Site\Values\Content;
use Netgen\IbexaSiteApi\Tests\Unit\Core\Site\ContentFieldsMockTrait;
use Netgen\IbexaSiteApi\Tests\Unit\Core\Site\QueryType\QueryTypeBaseTestCase;
use Netgen\TagsBundle\API\Repository\Values\Content\Query\Criterion\TagId;
use Netgen\TagsBundle\API\Repository\Values\Tags\Tag;
use Netgen\TagsBundle\Core\FieldType\Tags\Value as TagValue;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\Attributes\Group;
use Psr\Log\NullLogger;
use RuntimeException;

/**
 * TagFields Content Relation QueryType test case.
 *
 * @see \Netgen\IbexaSiteApi\Core\Site\QueryType\Content\Relations\TagFields
 *
 * @internal
 */
#[Group('query-type')]
#[AllowMockObjectsWithoutExpectations]
final class TagFieldsTest extends QueryTypeBaseTestCase
{
    private const string EXPECT_TEST_CONTENT = '__content__';

    use ContentFieldsMockTrait;

    public static function provideGetQueryCases(): array
    {
        return [
            [
                false,
                [
                    'content' => self::EXPECT_TEST_CONTENT,
                    'relation_field' => ['tags_a', 'tags_b'],
                    'limit' => 12,
                    'offset' => 34,
                    'sort' => 'published asc',
                ],
                new Query([
                    'filter' => new LogicalAnd([
                        new Visible(true),
                        new TagId([1, 2, 3, 4]),
                        new LogicalNot(new ContentId(42)),
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
                    'visible' => false,
                    'content' => self::EXPECT_TEST_CONTENT,
                    'exclude_self' => true,
                    'relation_field' => ['tags_a'],
                    'content_type' => 'article',
                    'field' => [],
                    'sort' => [
                        'published asc',
                    ],
                ],
                new Query([
                    'filter' => new LogicalAnd([
                        new Visible(false),
                        new ContentTypeIdentifier('article'),
                        new TagId([1, 2]),
                        new LogicalNot(new ContentId(42)),
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
                    'content' => self::EXPECT_TEST_CONTENT,
                    'exclude_self' => false,
                    'relation_field' => ['tags_b'],
                    'content_type' => 'article',
                    'field' => [
                        'title' => 'Hello',
                    ],
                    'sort' => [
                        'published desc',
                        'name asc',
                    ],
                ],
                new Query([
                    'filter' => new LogicalAnd([
                        new ContentTypeIdentifier('article'),
                        new Field('title', Operator::EQ, 'Hello'),
                        new TagId([3, 4]),
                    ]),
                    'sortClauses' => [
                        new DatePublished(Query::SORT_DESC),
                        new ContentName(Query::SORT_ASC),
                    ],
                ]),
            ],
            [
                true,
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
                new Query([
                    'filter' => new LogicalAnd([
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
                true,
                [
                    'visible' => true,
                    'content' => self::EXPECT_TEST_CONTENT,
                    'relation_field' => ['tags_a', 'tags_b'],
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
                new Query([
                    'filter' => new LogicalAnd([
                        new Visible(true),
                        new ContentTypeIdentifier('article'),
                        new Field('title', Operator::EQ, 'Hello'),
                        new Field('title', Operator::GTE, 7),
                        new TagId([1, 2, 3, 4]),
                        new LogicalNot(new ContentId(42)),
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
                    'relation_field' => ['tags_a', 'tags_b'],
                    'creation_date' => '4 May 2018',
                    'sort' => [
                        new DatePublished(Query::SORT_DESC),
                        new ContentName(Query::SORT_ASC),
                    ],
                ],
                new Query([
                    'filter' => new LogicalAnd([
                        new Visible(true),
                        new DateMetadata(
                            DateMetadata::CREATED,
                            Operator::EQ,
                            1525384800,
                        ),
                        new TagId([1, 2, 3, 4]),
                        new LogicalNot(new ContentId(42)),
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
        $this->expectException(InvalidArgumentException::class);

        $queryType = $this->getQueryTypeUnderTest();
        $content = $this->getTestContent();

        $queryType->getQuery([
            'content' => $content,
            'relation_field' => ['not_tags'],
            'content_type' => 'article',
            'sort' => 'published desc',
        ]);
    }

    public function testGetQueryWithNonexistentFieldFails(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Field "ćići" in Content #42 does not exist');

        $queryType = $this->getQueryTypeUnderTest();
        $content = $this->getTestContent();

        $queryType->getQuery([
            'content' => $content,
            'relation_field' => ['ćići'],
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
            'relation_field' => ['ćići'],
            'content_type' => 'article',
            'sort' => 'published desc',
        ]);

        self::assertEquals(
            new Query([
                'filter' => new LogicalAnd([
                    new Visible(true),
                    new ContentTypeIdentifier('article'),
                    new MatchNone(),
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
                    'relation_field' => ['tags_a', 'tags_b'],
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
                    'relation_field' => ['tags_a', 'tags_b'],
                    'sort' => 'just sort it',
                ],
            ],
        ];
    }

    protected function getQueryTypeName(): string
    {
        return 'SiteAPI:Content/Relations/TagFields';
    }

    protected function getQueryTypeUnderTest(bool $showHiddenItems = false): QueryType
    {
        return new TagFields(
            new Settings(
                ['eng-GB'],
                true,
                2,
                $showHiddenItems,
                true,
            ),
        );
    }

    protected function internalGetRepoFields(): array
    {
        return [
            new RepoField([
                'id' => 1,
                'fieldDefIdentifier' => 'tags_a',
                'value' => new TagValue([
                    new Tag([
                        'id' => 1,
                    ]),
                    new Tag([
                        'id' => 2,
                    ]),
                ]),
                'languageCode' => 'eng-GB',
                'fieldTypeIdentifier' => 'eztags',
            ]),
            new RepoField([
                'id' => 2,
                'fieldDefIdentifier' => 'tags_b',
                'value' => new TagValue([
                    new Tag([
                        'id' => 3,
                    ]),
                    new Tag([
                        'id' => 4,
                    ]),
                ]),
                'languageCode' => 'eng-GB',
                'fieldTypeIdentifier' => 'eztags',
            ]),
            new RepoField([
                'id' => 3,
                'fieldDefIdentifier' => 'not_tags',
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
                'identifier' => 'tags_a',
                'fieldTypeIdentifier' => 'eztags',
            ]),
            new FieldDefinition([
                'id' => 2,
                'identifier' => 'tags_b',
                'fieldTypeIdentifier' => 'eztags',
            ]),
            new FieldDefinition([
                'id' => 3,
                'identifier' => 'not_tags',
                'fieldTypeIdentifier' => 'ezstring',
            ]),
        ]);
    }

    protected function getTestContent(bool $failOnMissingField = true): Content
    {
        return new Content(
            [
                'id' => 42,
                'site' => $this->getSiteMock(),
                'name' => 'Krešo',
                'mainLocationId' => 123,
                'domainObjectMapper' => $this->getDomainObjectMapper($failOnMissingField),
                'repository' => $this->getRepositoryMock(),
                'innerVersionInfo' => $this->getRepoVersionInfo(),
                'languageCode' => 'eng-GB',
            ],
            $failOnMissingField,
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
            'content',
            'relation_field',
            'exclude_self',
        ];
    }

    protected function resolveExpectedMock(mixed $value): mixed
    {
        return match ($value) {
            self::EXPECT_TEST_CONTENT => $this->getTestContent(),
            default => $value,
        };
    }
}
