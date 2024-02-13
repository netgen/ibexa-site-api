<?php

declare(strict_types=1);

namespace Netgen\IbexaSiteApi\Tests\Unit\Core\Site\QueryType;

use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion\ContentTypeIdentifier;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion\DateMetadata;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion\Field;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion\IsFieldEmpty;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion\Location\Depth;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion\Location\IsMainLocation;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion\Location\Priority;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion\LogicalAnd;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion\LogicalNot;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion\Operator;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion\ParentLocationId;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion\Subtree;
use InvalidArgumentException;
use Netgen\IbexaSearchExtra\API\Values\Content\Query\Criterion\ObjectStateIdentifier;
use Netgen\IbexaSearchExtra\API\Values\Content\Query\Criterion\SectionIdentifier;
use Netgen\IbexaSearchExtra\API\Values\Content\Query\Criterion\Visible;
use Netgen\IbexaSiteApi\Core\Site\QueryType\CriteriaBuilder;
use Netgen\IbexaSiteApi\Core\Site\QueryType\CriterionDefinition;
use PHPUnit\Framework\TestCase;

/**
 * CriteriaBuilder test case.
 *
 * @group query-type
 *
 * @see \Netgen\IbexaSiteApi\Core\Site\QueryType\CriteriaBuilder
 *
 * @internal
 */
final class CriteriaBuilderTest extends TestCase
{
    protected ?CriteriaBuilder $criteriaBuilder = null;

    public function provideBuildCases(): iterable
    {
        return [
            [
                [
                    new CriterionDefinition([
                        'name' => 'content_type',
                        'target' => null,
                        'operator' => Operator::EQ,
                        'value' => 'article',
                    ]),
                ],
                [
                    new ContentTypeIdentifier('article'),
                ],
            ],
            [
                [
                    new CriterionDefinition([
                        'name' => 'content_type',
                        'target' => null,
                        'operator' => Operator::EQ,
                        'value' => 'article',
                    ]),
                    new CriterionDefinition([
                        'name' => 'content_type',
                        'target' => null,
                        'operator' => Operator::IN,
                        'value' => ['category', 'blog'],
                    ]),
                ],
                [
                    new ContentTypeIdentifier('article'),
                    new ContentTypeIdentifier(['category', 'blog']),
                ],
            ],
            [
                [
                    new CriterionDefinition([
                        'name' => 'section',
                        'target' => null,
                        'operator' => Operator::EQ,
                        'value' => 'standard',
                    ]),
                ],
                [
                    new SectionIdentifier('standard'),
                ],
            ],
            [
                [
                    new CriterionDefinition([
                        'name' => 'section',
                        'target' => null,
                        'operator' => Operator::EQ,
                        'value' => 'standard',
                    ]),
                    new CriterionDefinition([
                        'name' => 'section',
                        'target' => null,
                        'operator' => Operator::IN,
                        'value' => ['users', 'media'],
                    ]),
                ],
                [
                    new SectionIdentifier('standard'),
                    new SectionIdentifier(['users', 'media']),
                ],
            ],
            [
                [
                    new CriterionDefinition([
                        'name' => 'depth',
                        'target' => null,
                        'operator' => Operator::EQ,
                        'value' => 5,
                    ]),
                ],
                [
                    new Depth(Operator::EQ, 5),
                ],
            ],
            [
                [
                    new CriterionDefinition([
                        'name' => 'field',
                        'target' => 'title',
                        'operator' => Operator::EQ,
                        'value' => 'Hello',
                    ]),
                ],
                [
                    new Field('title', Operator::EQ, 'Hello'),
                ],
            ],
            [
                [
                    new CriterionDefinition([
                        'name' => 'state',
                        'target' => 'ez_lock',
                        'operator' => Operator::EQ,
                        'value' => 'locked',
                    ]),
                ],
                [
                    new ObjectStateIdentifier('ez_lock', 'locked'),
                ],
            ],
            [
                [
                    new CriterionDefinition([
                        'name' => 'not',
                        'target' => null,
                        'operator' => null,
                        'value' => [
                            new CriterionDefinition([
                                'name' => 'state',
                                'target' => 'ez_lock',
                                'operator' => Operator::EQ,
                                'value' => 'locked',
                            ]),
                        ],
                    ]),
                ],
                [
                    new LogicalNot(
                        new ObjectStateIdentifier('ez_lock', 'locked'),
                    ),
                ],
            ],
            [
                [
                    new CriterionDefinition([
                        'name' => 'not',
                        'target' => null,
                        'operator' => null,
                        'value' => null,
                    ]),
                ],
                [],
            ],
            [
                [
                    new CriterionDefinition([
                        'name' => 'main',
                        'target' => null,
                        'operator' => null,
                        'value' => true,
                    ]),
                ],
                [
                    new IsMainLocation(IsMainLocation::MAIN),
                ],
            ],
            [
                [
                    new CriterionDefinition([
                        'name' => 'main',
                        'target' => null,
                        'operator' => null,
                        'value' => false,
                    ]),
                ],
                [
                    new IsMainLocation(IsMainLocation::NOT_MAIN),
                ],
            ],
            [
                [
                    new CriterionDefinition([
                        'name' => 'main',
                        'target' => null,
                        'operator' => null,
                        'value' => null,
                    ]),
                ],
                [],
            ],
            [
                [
                    new CriterionDefinition([
                        'name' => 'priority',
                        'target' => null,
                        'operator' => Operator::GTE,
                        'value' => 5,
                    ]),
                ],
                [
                    new Priority(Operator::GTE, 5),
                ],
            ],
            [
                [
                    new CriterionDefinition([
                        'name' => 'creation_date',
                        'target' => null,
                        'operator' => Operator::BETWEEN,
                        'value' => [123, 456],
                    ]),
                ],
                [
                    new DateMetadata(DateMetadata::CREATED, Operator::BETWEEN, [123, 456]),
                ],
            ],
            [
                [
                    new CriterionDefinition([
                        'name' => 'visible',
                        'target' => null,
                        'operator' => null,
                        'value' => true,
                    ]),
                ],
                [
                    new Visible(true),
                ],
            ],
            [
                [
                    new CriterionDefinition([
                        'name' => 'visible',
                        'target' => null,
                        'operator' => null,
                        'value' => false,
                    ]),
                ],
                [
                    new Visible(false),
                ],
            ],
            [
                [
                    new CriterionDefinition([
                        'name' => 'visible',
                        'target' => null,
                        'operator' => null,
                        'value' => null,
                    ]),
                ],
                [],
            ],
            [
                [
                    new CriterionDefinition([
                        'name' => 'parent_location_id',
                        'target' => null,
                        'operator' => null,
                        'value' => 123,
                    ]),
                ],
                [
                    new ParentLocationId(123),
                ],
            ],
            [
                [
                    new CriterionDefinition([
                        'name' => 'parent_location_id',
                        'target' => null,
                        'operator' => null,
                        'value' => [123, 456],
                    ]),
                ],
                [
                    new ParentLocationId([123, 456]),
                ],
            ],
            [
                [
                    new CriterionDefinition([
                        'name' => 'subtree',
                        'target' => null,
                        'operator' => null,
                        'value' => '/oak/branch/',
                    ]),
                ],
                [
                    new Subtree('/oak/branch/'),
                ],
            ],
            [
                [
                    new CriterionDefinition([
                        'name' => 'subtree',
                        'target' => null,
                        'operator' => null,
                        'value' => ['/why/not/subroot/', '/ash/'],
                    ]),
                ],
                [
                    new Subtree(['/why/not/subroot/', '/ash/']),
                ],
            ],
            [
                [
                    new CriterionDefinition([
                        'name' => 'not',
                        'target' => null,
                        'operator' => null,
                        'value' => [
                            new CriterionDefinition([
                                'name' => 'subtree',
                                'target' => null,
                                'operator' => null,
                                'value' => ['/why/not/subroot/', '/ash/'],
                            ]),
                        ],
                    ]),
                ],
                [
                    new LogicalNot(
                        new Subtree(['/why/not/subroot/', '/ash/']),
                    ),
                ],
            ],
            [
                [
                    new CriterionDefinition([
                        'name' => 'not',
                        'target' => null,
                        'operator' => null,
                        'value' => [
                            new CriterionDefinition([
                                'name' => 'subtree',
                                'target' => null,
                                'operator' => null,
                                'value' => ['/subroot/tree/', '/ash/'],
                            ]),
                            new CriterionDefinition([
                                'name' => 'subtree',
                                'target' => null,
                                'operator' => null,
                                'value' => ['/subroot/tree/', '/poplar/'],
                            ]),
                        ],
                    ]),
                ],
                [
                    new LogicalNot(
                        new LogicalAnd([
                            new Subtree(['/subroot/tree/', '/ash/']),
                            new Subtree(['/subroot/tree/', '/poplar/']),
                        ]),
                    ),
                ],
            ],
            [
                [
                    new CriterionDefinition([
                        'name' => 'is_field_empty',
                        'target' => 'video',
                        'operator' => null,
                        'value' => false,
                    ]),
                ],
                [
                    new IsFieldEmpty('video', false),
                ],
            ],
            [
                [
                    new CriterionDefinition([
                        'name' => 'visible',
                        'target' => null,
                        'operator' => null,
                        'value' => false,
                    ]),
                ],
                [
                    new Visible(false),
                ],
            ],
        ];
    }

    /**
     * @dataProvider provideBuildCases
     *
     * @param \Netgen\IbexaSiteApi\Core\Site\QueryType\CriterionDefinition[] $arguments
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion[] $expectedCriteria
     */
    public function testBuild(array $arguments, array $expectedCriteria): void
    {
        $criteriaBuilder = $this->getCriteriaBuilderUnderTest();

        $criteria = $criteriaBuilder->build($arguments);

        self::assertEquals(
            $expectedCriteria,
            $criteria,
        );
    }

    public function testBuildUnsupportedCriterionThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Criterion named 'banana' is not handled");

        $criteriaBuilder = $this->getCriteriaBuilderUnderTest();

        $criteriaBuilder->build([
            new CriterionDefinition([
                'name' => 'banana',
            ]),
        ]);
    }

    public function testBuildDateMetadataThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("'someday' is invalid time string");

        $criteriaBuilder = $this->getCriteriaBuilderUnderTest();

        $criteriaBuilder->build([
            new CriterionDefinition([
                'name' => 'creation_date',
                'target' => null,
                'operator' => null,
                'value' => 'someday',
            ]),
        ]);
    }

    protected function getCriteriaBuilderUnderTest(): CriteriaBuilder
    {
        if ($this->criteriaBuilder === null) {
            $this->criteriaBuilder = new CriteriaBuilder();
        }

        return $this->criteriaBuilder;
    }
}
