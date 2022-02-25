<?php

declare(strict_types=1);

namespace Netgen\IbexaSiteApi\Tests\Unit\Core\Site;

use Ibexa\Contracts\Core\Repository\Exceptions\PropertyNotFoundException;
use Ibexa\Contracts\Core\Repository\Exceptions\PropertyReadOnlyException;
use Netgen\IbexaSiteApi\Core\Site\Settings;
use PHPUnit\Framework\TestCase;

/**
 * Settings value unit tests.
 *
 * @see \Netgen\IbexaSiteApi\API\Settings
 *
 * @internal
 */
final class SettingsTest extends TestCase
{
    public function testGetPrioritizedLanguages(): void
    {
        $settings = $this->getSettingsUnderTest();

        self::assertEquals(['cro-HR'], $settings->prioritizedLanguages);
    }

    public function testGetUseAlwaysAvailable(): void
    {
        $settings = $this->getSettingsUnderTest();

        self::assertTrue($settings->useAlwaysAvailable);
    }

    public function testGetRootLocationId(): void
    {
        $settings = $this->getSettingsUnderTest();

        self::assertEquals(42, $settings->rootLocationId);
    }

    public function testGetFailOnMissingField(): void
    {
        $settings = $this->getSettingsUnderTest();

        self::assertFalse($settings->failOnMissingField);
    }

    public function testGetNonexistentProperty(): void
    {
        $this->expectException(PropertyNotFoundException::class);

        $settings = $this->getSettingsUnderTest();

        $settings->blah;
    }

    public function testIssetPrioritizedLanguages(): void
    {
        $settings = $this->getSettingsUnderTest();

        self::assertTrue(isset($settings->prioritizedLanguages));
    }

    public function testIssetUseAlwaysAvailable(): void
    {
        $settings = $this->getSettingsUnderTest();

        self::assertTrue(isset($settings->useAlwaysAvailable));
    }

    public function testIssetRootLocationId(): void
    {
        $settings = $this->getSettingsUnderTest();

        self::assertTrue(isset($settings->rootLocationId));
    }

    public function testIssetNonexistentProperty(): void
    {
        $this->expectException(PropertyNotFoundException::class);

        $settings = $this->getSettingsUnderTest();

        self::assertFalse(isset($settings->blah));
    }

    public function testSet(): void
    {
        $this->expectException(PropertyReadOnlyException::class);

        $settings = $this->getSettingsUnderTest();

        $settings->rootLocationId = 24;
    }

    protected function getSettingsUnderTest(): Settings
    {
        return new Settings(
            ['cro-HR'],
            true,
            42,
            true,
            false
        );
    }
}
