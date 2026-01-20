<?php

declare(strict_types=1);

namespace Netgen\IbexaSiteApi\Tests\Unit\Core\Site\QueryType\Base;

use Ibexa\Contracts\Core\Repository\Values\Content\LocationQuery;
use Ibexa\Contracts\Core\Repository\Values\Content\Query;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Aggregation\RawTermAggregation;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion\DateMetadata;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion\FullText;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion\LogicalAnd;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion\Operator;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion\SectionId;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\SortClause\SectionIdentifier;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\SortClause\SectionName;
use Netgen\IbexaSearchExtra\API\Values\Content\Query\Criterion\Visible;
use Netgen\IbexaSiteApi\Core\Site\QueryType\QueryType;
use Netgen\IbexaSiteApi\Core\Site\Settings;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

/**
 * Custom QueryType test case.
 *
 * @internal
 */
#[Group('query-type')]
final class CustomQueryTypeTest extends TestCase
{
    public function testGetName(): void
    {
        $queryType = $this->getQueryTypeUnderTest();

        self::assertSame(
            'Test:Custom',
            $queryType::getName(),
        );
    }

    public function testGetSupportedParameters(): void
    {
        $queryType = $this->getQueryTypeUnderTest();

        self::assertSame(
            [
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
                'prefabrication_date',
            ],
            $queryType->getSupportedParameters(),
        );
    }

    public static function provideGetQueryCases(): iterable
    {
        return [
            [
                [
                    'prefabrication_date' => 123,
                    'sort' => 'section',
                ],
                new LocationQuery([
                    'filter' => new LogicalAnd([
                        new Visible(true),
                        new DateMetadata(
                            DateMetadata::MODIFIED,
                            Operator::EQ,
                            123,
                        ),
                        new SectionId(42),
                    ]),
                    'query' => new FullText('one AND two OR three'),
                    'sortClauses' => [
                        new SectionIdentifier(),
                    ],
                    'aggregations' => [
                        new RawTermAggregation('name', 'field_name'),
                    ],
                ]),
            ],
            [
                [
                    'visible' => false,
                    'prefabrication_date' => [123, 456],
                    'sort' => [
                        'whatever',
                        'section',
                    ],
                ],
                new LocationQuery([
                    'filter' => new LogicalAnd([
                        new Visible(false),
                        new DateMetadata(
                            DateMetadata::MODIFIED,
                            Operator::IN,
                            [123, 456],
                        ),
                        new SectionId(42),
                    ]),
                    'query' => new FullText('one AND two OR three'),
                    'sortClauses' => [
                        new SectionName(),
                        new SectionIdentifier(),
                    ],
                    'aggregations' => [
                        new RawTermAggregation('name', 'field_name'),
                    ],
                ]),
            ],
            [
                [
                    'visible' => null,
                    'prefabrication_date' => [
                        'eq' => 123,
                        'in' => [123, 456],
                    ],
                ],
                new LocationQuery([
                    'filter' => new LogicalAnd([
                        new DateMetadata(
                            DateMetadata::MODIFIED,
                            Operator::EQ,
                            123,
                        ),
                        new DateMetadata(
                            DateMetadata::MODIFIED,
                            Operator::IN,
                            [123, 456],
                        ),
                        new SectionId(42),
                    ]),
                    'query' => new FullText('one AND two OR three'),
                    'aggregations' => [
                        new RawTermAggregation('name', 'field_name'),
                    ],
                ]),
            ],
        ];
    }

    #[DataProvider('provideGetQueryCases')]
    public function testGetQuery(array $parameters, Query $expectedQuery): void
    {
        $queryType = $this->getQueryTypeUnderTest();

        $query = $queryType->getQuery($parameters);

        self::assertEquals(
            $expectedQuery,
            $query,
        );
    }

    protected function getQueryTypeUnderTest(): QueryType
    {
        return new CustomQueryType(
            new Settings(
                ['eng-GB'],
                true,
                2,
                false,
                true,
            ),
        );
    }
}
