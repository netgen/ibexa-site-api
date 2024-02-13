<?php

declare(strict_types=1);

namespace Netgen\IbexaSiteApi\Tests\Unit\Core\Site\QueryType\Location;

use Ibexa\Contracts\Core\Repository\Values\Content\LocationQuery;
use Ibexa\Contracts\Core\Repository\Values\Content\Query;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion\ContentTypeIdentifier;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion\DateMetadata;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion\Field;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion\Location\Depth as DepthCriterion;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion\Location\Priority as PriorityCriterion;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion\LogicalAnd;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion\Operator;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion\ParentLocationId;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion\Subtree;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\SortClause\DatePublished;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\SortClause\Location\Depth as DepthSortClause;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\SortClause\Location\Priority;
use Netgen\IbexaSearchExtra\API\Values\Content\Query\Criterion\SectionIdentifier;
use Netgen\IbexaSearchExtra\API\Values\Content\Query\Criterion\Visible;
use Netgen\IbexaSearchExtra\API\Values\Content\Query\SortClause\ContentName;
use Netgen\IbexaSiteApi\Core\Site\QueryType\Location\Fetch;
use Netgen\IbexaSiteApi\Core\Site\QueryType\QueryType;
use Netgen\IbexaSiteApi\Core\Site\Settings;
use Netgen\IbexaSiteApi\Tests\Unit\Core\Site\QueryType\QueryTypeBaseTest;

/**
 * Fetch Location QueryType test case.
 *
 * @group query-type
 *
 * @internal
 */
final class FetchTest extends QueryTypeBaseTest
{
    public function provideGetQueryCases(): array
    {
        return [
            [
                false,
                [],
                new LocationQuery([
                    'filter' => new Visible(true),
                ]),
            ],
            [
                false,
                [
                    'visible' => false,
                    'priority' => null,
                    'main' => null,
                    'sort' => 'published asc',
                ],
                new LocationQuery([
                    'filter' => new Visible(false),
                    'sortClauses' => [
                        new DatePublished(Query::SORT_ASC),
                    ],
                ]),
            ],
            [
                false,
                [
                    'visible' => null,
                    'limit' => 12,
                    'offset' => 34,
                    'priority' => [
                        'between' => [2, 7],
                    ],
                    'sort' => 'priority desc',
                ],
                new LocationQuery([
                    'limit' => 12,
                    'offset' => 34,
                    'filter' => new PriorityCriterion(Operator::BETWEEN, [2, 7]),
                    'sortClauses' => [
                        new Priority(Query::SORT_DESC),
                    ],
                ]),
            ],
            [
                true,
                [
                    'content_type' => 'article',
                    'sort' => [
                        'published asc',
                    ],
                ],
                new LocationQuery([
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
                    'section' => 'standard',
                    'field' => [],
                    'depth' => 5,
                    'sort' => [
                        'published desc',
                        'depth asc',
                    ],
                ],
                new LocationQuery([
                    'filter' => new LogicalAnd([
                        new Visible(true),
                        new SectionIdentifier('standard'),
                        new DepthCriterion(Operator::EQ, 5),
                    ]),
                    'sortClauses' => [
                        new DatePublished(Query::SORT_DESC),
                        new DepthSortClause(Query::SORT_ASC),
                    ],
                ]),
            ],
            [
                false,
                [
                    'section' => [
                        'eq' => 'standard',
                    ],
                    'parent_location_id' => null,
                    'subtree' => null,
                    'field' => [
                        'title' => 'Hello',
                    ],
                    'sort' => new DatePublished(Query::SORT_DESC),
                ],
                new LocationQuery([
                    'filter' => new LogicalAnd([
                        new Visible(true),
                        new SectionIdentifier('standard'),
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
                    'section' => [
                        'in' => [
                            'standard',
                        ],
                    ],
                    'parent_location_id' => 42,
                    'subtree' => '/1/2/42/',
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
                        new SectionIdentifier(['standard']),
                        new ParentLocationId(42),
                        new Subtree('/1/2/42/'),
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
                    'parent_location_id' => [24, 42],
                    'subtree' => ['/1/2/42/', '/2/3/4/'],
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
                        new ParentLocationId([24, 42]),
                        new Subtree(['/1/2/42/', '/2/3/4/']),
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
                ],
                new LocationQuery([
                    'filter' => new LogicalAnd([
                        new Visible(true),
                        new DateMetadata(
                            DateMetadata::CREATED,
                            Operator::EQ,
                            1525384800,
                        ),
                    ]),
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
        return 'SiteAPI:Location/Fetch';
    }

    protected function getQueryTypeUnderTest(bool $showHiddenItems = false): QueryType
    {
        return new Fetch(
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
            'depth',
            'main',
            'parent_location_id',
            'priority',
            'subtree',
        ];
    }
}
