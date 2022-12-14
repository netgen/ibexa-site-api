<?php

declare(strict_types=1);

namespace Netgen\IbexaSiteApi\Tests\Unit\Core\Site\QueryType;

use Ibexa\Contracts\Core\Repository\Values\Content\Query;
use InvalidArgumentException;
use Netgen\IbexaSiteApi\Core\Site\QueryType\QueryType;
use PHPUnit\Framework\TestCase;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use function array_flip;
use function count;
use function md5;
use function time;

/**
 * Base QueryType test case.
 */
abstract class QueryTypeBaseTest extends TestCase
{
    public function testGetName(): void
    {
        $queryType = $this->getQueryTypeUnderTest();

        self::assertSame(
            $this->getQueryTypeName(),
            $queryType::getName(),
        );
    }

    public function testGetSupportedParameters(): void
    {
        $queryType = $this->getQueryTypeUnderTest();

        self::assertSame(
            $this->getSupportedParameters(),
            $queryType->getSupportedParameters(),
        );
    }

    public function testSupportsParameterReturnsTrue(): void
    {
        $queryType = $this->getQueryTypeUnderTest();

        foreach ($this->getSupportedParameters() as $parameter) {
            self::assertTrue($queryType->supportsParameter($parameter));
        }
    }

    public function testSupportsParameterReturnsFalse(): void
    {
        $queryType = $this->getQueryTypeUnderTest();

        self::assertFalse($queryType->supportsParameter(md5((string) time())));
    }

    public function testGetBaseSupportedParameters(): void
    {
        $queryType = $this->getQueryTypeUnderTest();
        $parameters = $queryType->getSupportedParameters();

        $expectedParameters = [
            'content_type',
            'field',
            'is_field_empty',
            'creation_date',
            'section',
            'state',
            'sort',
            'limit',
            'offset',
        ];

        self::assertGreaterThanOrEqual(count($expectedParameters), count($parameters));
        $parameterSet = array_flip($parameters);

        foreach ($expectedParameters as $expectedParameter) {
            self::assertArrayHasKey($expectedParameter, $parameterSet);
            self::assertTrue($queryType->supportsParameter($expectedParameter));
        }
    }

    abstract public function providerForTestGetQuery();

    /**
     * @dataProvider providerForTestGetQuery
     */
    public function testGetQuery(bool $showHiddenItems, array $parameters, Query $expectedQuery): void
    {
        $queryType = $this->getQueryTypeUnderTest($showHiddenItems);

        $query = $queryType->getQuery($parameters);

        self::assertEquals(
            $expectedQuery,
            $query,
        );
    }

    abstract public function providerForTestGetQueryWithInvalidOptions();

    /**
     * @group yyy
     * @dataProvider providerForTestGetQueryWithInvalidOptions
     */
    public function testGetQueryWithInvalidOptions(array $parameters): void
    {
        $this->expectException(InvalidOptionsException::class);

        $queryType = $this->getQueryTypeUnderTest();

        $queryType->getQuery($parameters);
    }

    abstract public function providerForTestGetQueryWithInvalidCriteria();

    /**
     * @dataProvider providerForTestGetQueryWithInvalidCriteria
     */
    public function testGetQueryWithInvalidCriteria(array $parameters): void
    {
        $this->expectException(InvalidArgumentException::class);

        $queryType = $this->getQueryTypeUnderTest();

        $queryType->getQuery($parameters);
    }

    abstract public function providerForTestInvalidSortClauseThrowsException();

    /**
     * @dataProvider providerForTestInvalidSortClauseThrowsException
     */
    public function testInvalidSortClauseThrowsException(array $parameters): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageMatches("/Sort string '.*' was not converted to a SortClause/");

        $queryType = $this->getQueryTypeUnderTest();

        $queryType->getQuery($parameters);
    }

    abstract protected function getQueryTypeUnderTest(bool $showHiddenItems = false): QueryType;

    abstract protected function getQueryTypeName(): string;

    /**
     * @return string[]
     */
    abstract protected function getSupportedParameters(): array;
}
