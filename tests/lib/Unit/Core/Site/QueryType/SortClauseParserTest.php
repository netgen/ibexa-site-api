<?php

declare(strict_types=1);

namespace Netgen\IbexaSiteApi\Tests\Unit\Core\Site\QueryType;

use Ibexa\Contracts\Core\Repository\Values\Content\Query;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\SortClause;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\SortClause\DateModified;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\SortClause\DatePublished;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\SortClause\Field;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\SortClause\Location\Depth;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\SortClause\Location\Priority;
use InvalidArgumentException;
use Netgen\IbexaSearchExtra\API\Values\Content\Query\SortClause\ContentName;
use Netgen\IbexaSiteApi\Core\Site\QueryType\SortClauseParser;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

use function preg_quote;
use function sprintf;

/**
 * SortClauseParser test case.
 *
 * @see \Netgen\IbexaSiteApi\Core\Site\QueryType\SortClauseParser
 *
 * @internal
 */
#[Group('query-type')]
#[Group('sort')]
final class SortClauseParserTest extends TestCase
{
    public static function provideParseValidCases(): iterable
    {
        return [
            [
                'depth',
                new Depth(Query::SORT_ASC),
            ],
            [
                'depth asc',
                new Depth(Query::SORT_ASC),
            ],
            [
                'depth desc',
                new Depth(Query::SORT_DESC),
            ],
            [
                'field/article/title',
                new Field('article', 'title', Query::SORT_ASC),
            ],
            [
                'field/article/title asc',
                new Field('article', 'title', Query::SORT_ASC),
            ],
            [
                'field/article/title desc',
                new Field('article', 'title', Query::SORT_DESC),
            ],
            [
                'modified',
                new DateModified(Query::SORT_ASC),
            ],
            [
                'modified asc',
                new DateModified(Query::SORT_ASC),
            ],
            [
                'modified desc',
                new DateModified(Query::SORT_DESC),
            ],
            [
                'name',
                new ContentName(Query::SORT_ASC),
            ],
            [
                'name asc',
                new ContentName(Query::SORT_ASC),
            ],
            [
                'name desc',
                new ContentName(Query::SORT_DESC),
            ],
            [
                'priority',
                new Priority(Query::SORT_ASC),
            ],
            [
                'priority asc',
                new Priority(Query::SORT_ASC),
            ],
            [
                'priority desc',
                new Priority(Query::SORT_DESC),
            ],
            [
                'published',
                new DatePublished(Query::SORT_ASC),
            ],
            [
                'published asc',
                new DatePublished(Query::SORT_ASC),
            ],
            [
                'published desc',
                new DatePublished(Query::SORT_DESC),
            ],
        ];
    }

    #[DataProvider('provideParseValidCases')]
    public function testParseValid(string $stringDefinition, SortClause $expectedSortClause): void
    {
        $parser = $this->getParserUnderTest();

        $sortClause = $parser->parse($stringDefinition);

        self::assertEquals($sortClause, $expectedSortClause);
    }

    public static function provideParseInvalidCases(): iterable
    {
        return [
            [
                'blort',
                "Could not handle sort type 'blort'",
            ],
            [
                'published argh',
                "Could not handle sort direction 'argh'",
            ],
            [
                'field asc',
                'Field sort clause requires ContentType identifier',
            ],
            [
                'field/type asc',
                'Field sort clause requires FieldDefinition identifier',
            ],
            [
                'field/article/title argh',
                "Could not handle sort direction 'argh'",
            ],
        ];
    }

    #[DataProvider('provideParseInvalidCases')]
    public function testParseInvalid(string $stringDefinition, string $message): void
    {
        $this->expectException(InvalidArgumentException::class);
        $message = preg_quote($message, '/');
        $this->expectExceptionMessageMatches(sprintf('/%s/', $message));

        $parser = $this->getParserUnderTest();
        $parser->parse($stringDefinition);
    }

    protected function getParserUnderTest(): SortClauseParser
    {
        return new SortClauseParser();
    }
}
