<?php

declare(strict_types=1);

namespace Netgen\IbexaSiteApi\Tests\Unit\Core\Site\QueryType\Location\Relations;

use Ibexa\Contracts\Core\Repository\Values\Content\LocationQuery;
use Ibexa\Contracts\Core\Repository\Values\Content\Query;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion\ContentTypeIdentifier;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion\DateMetadata;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion\Field;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion\FieldRelation;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion\Location\IsMainLocation;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion\LogicalAnd;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion\MatchNone;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion\Operator;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\SortClause\DatePublished;
use Ibexa\Core\Repository\Values\ContentType\FieldDefinitionCollection;
use Netgen\IbexaSearchExtra\API\Values\Content\Query\Criterion\Visible;
use Netgen\IbexaSearchExtra\API\Values\Content\Query\SortClause\ContentName;
use Netgen\IbexaSiteApi\Core\Site\QueryType\Location\Relations\ReverseFields;
use Netgen\IbexaSiteApi\Core\Site\QueryType\QueryType;
use Netgen\IbexaSiteApi\Core\Site\Settings;
use Netgen\IbexaSiteApi\Core\Site\Values\Content;
use Netgen\IbexaSiteApi\Tests\Unit\Core\Site\ContentFieldsMockTrait;
use Netgen\IbexaSiteApi\Tests\Unit\Core\Site\QueryType\QueryTypeBaseTest;
use Netgen\TagsBundle\API\Repository\Values\Content\Query\Criterion\TagId;
use Psr\Log\NullLogger;

/**
 * ReverseFields Location Relation QueryType test case.
 *
 * @group query-type
 *
 * @see \Netgen\IbexaSiteApi\Core\Site\QueryType\Location\Relations\ReverseFields
 *
 * @internal
 */
final class ReverseFieldsTest extends QueryTypeBaseTest
{
    use ContentFieldsMockTrait;

    public function providerForTestGetQuery(): array
    {
        $content = $this->getTestContent();

        return [
            [
                false,
                [
                    'content' => $content,
                    'relation_field' => ['relations_a', 'relations_b'],
                    'limit' => 12,
                    'offset' => 34,
                    'sort' => 'published asc',
                ],
                new LocationQuery([
                    'filter' => new LogicalAnd([
                        new Visible(true),
                        new IsMainLocation(IsMainLocation::MAIN),
                        new FieldRelation('relations_a', Operator::CONTAINS, [42]),
                        new FieldRelation('relations_b', Operator::CONTAINS, [42]),
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
                    'content' => $content,
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
                        new FieldRelation('relations_a', Operator::CONTAINS, [42]),
                    ]),
                    'sortClauses' => [
                        new DatePublished(Query::SORT_ASC),
                    ],
                ]),
            ],
            [
                false,
                [
                    'content' => $content,
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
                        new FieldRelation('relations_b', Operator::CONTAINS, [42]),
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
                    'content' => $content,
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
                    'content' => $content,
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
                        new FieldRelation('relations_a', Operator::CONTAINS, [42]),
                        new FieldRelation('relations_b', Operator::CONTAINS, [42]),
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
                    'content' => $content,
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
                        new FieldRelation('relations_a', Operator::CONTAINS, [42]),
                        new FieldRelation('relations_b', Operator::CONTAINS, [42]),
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
                    'content' => $content,
                    'relation_field' => ['relations_a', 'relations_b'],
                    'tag_id' => 223,
                ],
                new LocationQuery([
                    'filter' => new LogicalAnd([
                        new IsMainLocation(IsMainLocation::MAIN),
                        new Visible(true),
                        new TagId(223),
                        new FieldRelation('relations_a', Operator::CONTAINS, [42]),
                        new FieldRelation('relations_b', Operator::CONTAINS, [42]),
                    ]),
                ]),
            ],
            [
                true,
                [
                    'content' => $content,
                    'relation_field' => ['relations_a', 'relations_b'],
                    'tag_id' => [223, 224, 1],
                ],
                new LocationQuery([
                    'filter' => new LogicalAnd([
                        new IsMainLocation(IsMainLocation::MAIN),
                        new Visible(true),
                        new TagId([223, 224, 1]),
                        new FieldRelation('relations_a', Operator::CONTAINS, [42]),
                        new FieldRelation('relations_b', Operator::CONTAINS, [42]),
                    ]),
                ]),
            ],
            [
                true,
                [
                    'content' => $content,
                    'relation_field' => ['relations_a', 'relations_b'],
                    'tag_id' => [
                        'eq' => 225,
                    ],
                ],
                new LocationQuery([
                    'filter' => new LogicalAnd([
                        new IsMainLocation(IsMainLocation::MAIN),
                        new Visible(true),
                        new TagId(225),
                        new FieldRelation('relations_a', Operator::CONTAINS, [42]),
                        new FieldRelation('relations_b', Operator::CONTAINS, [42]),
                    ]),
                ]),
            ],
            [
                true,
                [
                    'content' => $content,
                    'relation_field' => ['relations_a', 'relations_b'],
                    'tag_id' => [
                        'in' => [225, 226],
                    ],
                ],
                new LocationQuery([
                    'filter' => new LogicalAnd([
                        new IsMainLocation(IsMainLocation::MAIN),
                        new Visible(true),
                        new TagId([225, 226]),
                        new FieldRelation('relations_a', Operator::CONTAINS, [42]),
                        new FieldRelation('relations_b', Operator::CONTAINS, [42]),
                    ]),
                ]),
            ],
        ];
    }

    public function providerForTestGetQueryWithInvalidOptions(): array
    {
        $content = $this->getTestContent();

        return [
            [
                [
                    'content' => $content,
                    'relation_field' => 'field',
                    'content_type' => 1,
                ],
            ],
            [
                [
                    'content' => $content,
                    'relation_field' => 'field',
                    'field' => 1,
                ],
            ],
            [
                [
                    'content' => $content,
                    'relation_field' => 'field',
                    'creation_date' => true,
                ],
            ],
            [
                [
                    'content' => $content,
                    'relation_field' => 'field',
                    'limit' => 'five',
                ],
            ],
            [
                [
                    'content' => $content,
                    'relation_field' => 'field',
                    'offset' => 'ten',
                ],
            ],
            [
                [
                    'content' => $content,
                    'relation_field' => [1],
                ],
            ],
            [
                [
                    'content' => $content,
                    'relation_field' => 'field',
                    'tag_id' => 'ten',
                ],
            ],
        ];
    }

    public function providerForTestGetQueryWithInvalidCriteria(): array
    {
        $content = $this->getTestContent();

        return [
            [
                [
                    'content' => $content,
                    'relation_field' => ['relations_a', 'relations_b'],
                    'creation_date' => [
                        'like' => 5,
                    ],
                ],
            ],
        ];
    }

    public function providerForTestInvalidSortClauseThrowsException(): array
    {
        $content = $this->getTestContent();

        return [
            [
                [
                    'content' => $content,
                    'relation_field' => ['relations_a', 'relations_b'],
                    'sort' => 'just sort it',
                ],
            ],
        ];
    }

    protected function getQueryTypeName(): string
    {
        return 'SiteAPI:Location/Relations/ReverseFields';
    }

    protected function getQueryTypeUnderTest(bool $showHiddenItems = false): QueryType
    {
        return new ReverseFields(
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
        return [];
    }

    protected function internalGetRepoFieldDefinitions(): FieldDefinitionCollection
    {
        return new FieldDefinitionCollection();
    }

    protected function getTestContent(): Content
    {
        return new Content(
            [
                'id' => 42,
                'site' => $this->getSiteMock(),
                'name' => 'Krešo',
                'mainLocationId' => 123,
                'domainObjectMapper' => $this->getDomainObjectMapper(),
                'repository' => $this->getRepositoryMock(),
                'innerVersionInfo' => $this->getRepoVersionInfo(),
                'languageCode' => 'eng-GB',
            ],
            true,
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
            'tag_id',
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
}
