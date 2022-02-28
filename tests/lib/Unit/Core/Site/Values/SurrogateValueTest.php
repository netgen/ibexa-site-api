<?php

declare(strict_types=1);

namespace Netgen\IbexaSiteApi\Tests\Unit\Core\Site\Values;

use Netgen\IbexaSiteApi\Core\Site\Values\Field\SurrogateValue;
use PHPUnit\Framework\TestCase;

/**
 * Surrogate Field value unit tests.
 *
 * @group fields
 *
 * @see \Netgen\IbexaSiteApi\Core\Site\Values\Field\SurrogateValue
 *
 * @internal
 */
final class SurrogateValueTest extends TestCase
{
    public function testConstructWithArbitraryArguments(): void
    {
        $value = new SurrogateValue(1, 2, 'three');

        self::assertInstanceOf(SurrogateValue::class, $value);
    }

    public function testGetPropertyReturnsNull(): void
    {
        $value = new SurrogateValue();

        self::assertNull($value->property);
    }

    public function testCallMethodReturnsNull(): void
    {
        $value = new SurrogateValue();

        self::assertNull($value->method());
    }

    public function testCastToStringReturnsEmptyString(): void
    {
        $value = new SurrogateValue();

        self::assertSame('', (string) $value);
    }

    public function testCheckingForPropertyReturnsFalse(): void
    {
        $value = new SurrogateValue();

        self::assertFalse(isset($value->property));
    }

    public function testSettingPropertyDoesNothing(): void
    {
        $value = new SurrogateValue();

        $value->property = 1;

        $this->addToAssertionCount(1);
    }

    public function testUnsettingPropertyDoesNothing(): void
    {
        $value = new SurrogateValue();

        unset($value->property);

        $this->addToAssertionCount(1);
    }
}
