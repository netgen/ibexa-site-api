<?php

declare(strict_types=1);

namespace Netgen\IbexaSiteApi\Tests\Unit\Core\Site\QueryType\Base;

use Ibexa\Contracts\Core\Repository\Values\Content\Query;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion\ContentTypeIdentifier;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion\DateMetadata;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion\Field;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion\IsFieldEmpty;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion\LogicalAnd;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion\Operator;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\SortClause\DateModified;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\SortClause\DatePublished;
use Netgen\IbexaSearchExtra\API\Values\Content\Query\Criterion\Visible;
use Netgen\IbexaSearchExtra\API\Values\Content\Query\SortClause\ContentName;
use Netgen\IbexaSiteApi\Core\Site\QueryType\QueryType;
use Netgen\IbexaSiteApi\Core\Site\Settings;
use Netgen\IbexaSiteApi\Tests\Unit\Core\Site\QueryType\QueryTypeBaseTest;

/**
 * Base QueryType stub test case.
 *
 * @group query-type
 *
 * @see \Netgen\IbexaSiteApi\Core\Site\QueryType\Base
 *
 * @internal
 */
final class BaseQueryTypeTest extends QueryTypeBaseTest
{
    public function provideGetQueryCases(): array
    {
        return [
            [
                false,
                [],
                new Query([
                    'filter' => new Visible(true),
                ]),
            ],
            [
                false,
                [
                    'visible' => false,
                    'limit' => 12,
                    'offset' => 34,
                    'sort' => 'published asc',
                ],
                new Query([
                    'filter' => new Visible(false),
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
                    'visible' => null,
                    'content_type' => 'article',
                    'sort' => 'published desc',
                ],
                new Query([
                    'filter' => new ContentTypeIdentifier('article'),
                    'sortClauses' => [
                        new DatePublished(Query::SORT_DESC),
                    ],
                ]),
            ],
            [
                true,
                [
                    'content_type' => 'article',
                    'field' => [],
                    'sort' => [
                        'published asc',
                    ],
                ],
                new Query([
                    'filter' => new ContentTypeIdentifier('article'),
                    'sortClauses' => [
                        new DatePublished(Query::SORT_ASC),
                    ],
                ]),
            ],
            [
                true,
                [
                    'visible' => true,
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
                        new Visible(true),
                        new ContentTypeIdentifier('article'),
                        new Field('title', Operator::EQ, 'Hello'),
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
                        new Visible(true),
                        new ContentTypeIdentifier('article'),
                        new Field('title', Operator::EQ, 'Hello'),
                    ]),
                    'sortClauses' => [
                        new DatePublished(Query::SORT_DESC),
                    ],
                ]),
            ],
            [
                false,
                [
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
                    'is_field_empty' => null,
                    'modification_date' => '8 October 2019',
                    'sort' => 'modified desc',
                ],
                new Query([
                    'filter' => new LogicalAnd([
                        new Visible(true),
                        new DateMetadata(
                            DateMetadata::MODIFIED,
                            Operator::EQ,
                            1570485600,
                        ),
                    ]),
                    'sortClauses' => [
                        new DateModified(Query::SORT_DESC),
                    ],
                ]),
            ],
            [
                false,
                [
                    'is_field_empty' => [
                        'image' => false,
                        'video' => true,
                        'audio' => null,
                    ],
                    'sort' => 'published desc',
                ],
                new Query([
                    'filter' => new LogicalAnd([
                        new Visible(true),
                        new IsFieldEmpty('image', false),
                        new IsFieldEmpty('video', true),
                    ]),
                    'sortClauses' => [
                        new DatePublished(Query::SORT_DESC),
                    ],
                ]),
            ],
            [
                false,
                [
                    'is_field_empty' => [
                        'image' => null,
                    ],
                    'sort' => 'published desc',
                ],
                new Query([
                    'filter' => new Visible(true),
                    'sortClauses' => [
                        new DatePublished(Query::SORT_DESC),
                    ],
                ]),
            ],
        ];
    }

    public function provideGetQueryWithInvalidOptionsCases(): array
    {
        return [
            [
                [
                    'content_type' => 1,
                ],
            ],
            [
                [
                    'field' => 1,
                ],
            ],
            [
                [
                    'creation_date' => true,
                ],
            ],
            [
                [
                    'limit' => 'five',
                ],
            ],
            [
                [
                    'offset' => 'ten',
                ],
            ],
            [
                [
                    'is_field_empty' => [
                        'field' => 'not empty',
                    ],
                ],
            ],
            [
                [
                    'is_field_empty' => [true],
                ],
            ],
        ];
    }

    public function provideGetQueryWithInvalidCriteriaCases(): array
    {
        return [
            [
                [
                    'creation_date' => [
                        'like' => 5,
                    ],
                ],
            ],
        ];
    }

    public function provideInvalidSortClauseThrowsExceptionCases(): array
    {
        return [
            [
                [
                    'sort' => 'just sort it',
                ],
            ],
        ];
    }

    protected function getQueryTypeName(): string
    {
        return 'Test:Base';
    }

    protected function getQueryTypeUnderTest(bool $showHiddenItems = false): QueryType
    {
        return new BaseQueryType(
            new Settings(
                ['eng-GB'],
                true,
                2,
                $showHiddenItems,
                true,
            ),
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
        ];
    }
}
