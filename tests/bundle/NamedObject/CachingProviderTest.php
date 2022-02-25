<?php

declare(strict_types=1);

namespace Netgen\Bundle\IbexaSiteApiBundle\Tests\NamedObject;

use Netgen\Bundle\IbexaSiteApiBundle\NamedObject\Provider;
use Netgen\Bundle\IbexaSiteApiBundle\NamedObject\Provider\Caching;
use Netgen\IbexaSiteApi\API\Values\Content;
use Netgen\IbexaSiteApi\API\Values\Location;
use Netgen\TagsBundle\API\Repository\Values\Tags\Tag;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/**
 * @internal
 */
final class CachingProviderTest extends TestCase
{
    protected $mockedProvider;

    public function testHasContentReturnsTrue(): void
    {
        $providerMock = $this->getProviderMock();
        $provider = $this->getProviderUnderTest($providerMock);

        $providerMock
            ->method('hasContent')
            ->with('apple')
            ->willReturn(true);

        self::assertTrue($provider->hasContent('apple'));
    }

    public function testHasContentReturnsFalse(): void
    {
        $providerMock = $this->getProviderMock();
        $provider = $this->getProviderUnderTest($providerMock);

        $providerMock
            ->method('hasContent')
            ->with('fig')
            ->willReturn(false);

        self::assertFalse($provider->hasContent('fig'));
    }

    public function testGetContent(): void
    {
        $providerMock = $this->getProviderMock();
        $provider = $this->getProviderUnderTest($providerMock);

        $providerMock
            ->expects(self::once())
            ->method('getContent')
            ->with('apple')
            ->willReturn($this->getContentMock());

        self::assertSame($this->getContentMock(), $provider->getContent('apple'));
        self::assertSame($this->getContentMock(), $provider->getContent('apple'));
    }

    public function testGetContentThrowsException(): void
    {
        $providerMock = $this->getProviderMock();
        $provider = $this->getProviderUnderTest($providerMock);

        $providerMock
            ->expects(self::once())
            ->method('getContent')
            ->with('fig')
            ->willThrowException(new RuntimeException('content not found'));

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('content not found');

        $provider->getContent('fig');
    }

    public function testHasLocationReturnsTrue(): void
    {
        $providerMock = $this->getProviderMock();
        $provider = $this->getProviderUnderTest($providerMock);

        $providerMock
            ->method('hasLocation')
            ->with('apple')
            ->willReturn(true);

        self::assertTrue($provider->hasLocation('apple'));
    }

    public function testHasLocationReturnsFalse(): void
    {
        $providerMock = $this->getProviderMock();
        $provider = $this->getProviderUnderTest($providerMock);

        $providerMock
            ->method('hasLocation')
            ->with('fig')
            ->willReturn(false);

        self::assertFalse($provider->hasLocation('fig'));
    }

    public function testGetLocation(): void
    {
        $providerMock = $this->getProviderMock();
        $provider = $this->getProviderUnderTest($providerMock);

        $providerMock
            ->expects(self::once())
            ->method('getLocation')
            ->with('apple')
            ->willReturn($this->getLocationMock());

        self::assertSame($this->getLocationMock(), $provider->getLocation('apple'));
        self::assertSame($this->getLocationMock(), $provider->getLocation('apple'));
    }

    public function testGetLocationThrowsException(): void
    {
        $providerMock = $this->getProviderMock();
        $provider = $this->getProviderUnderTest($providerMock);

        $providerMock
            ->expects(self::once())
            ->method('getLocation')
            ->with('fig')
            ->willThrowException(new RuntimeException('location not found'));

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('location not found');

        $provider->getLocation('fig');
    }

    public function testHasTagReturnsTrue(): void
    {
        $providerMock = $this->getProviderMock();
        $provider = $this->getProviderUnderTest($providerMock);

        $providerMock
            ->method('hasTag')
            ->with('apple')
            ->willReturn(true);

        self::assertTrue($provider->hasTag('apple'));
    }

    public function testHasTagReturnsFalse(): void
    {
        $providerMock = $this->getProviderMock();
        $provider = $this->getProviderUnderTest($providerMock);

        $providerMock
            ->method('hasTag')
            ->with('fig')
            ->willReturn(false);

        self::assertFalse($provider->hasTag('fig'));
    }

    public function testGetTag(): void
    {
        $providerMock = $this->getProviderMock();
        $provider = $this->getProviderUnderTest($providerMock);

        $tag = new Tag();

        $providerMock
            ->expects(self::once())
            ->method('getTag')
            ->with('apple')
            ->willReturn($tag);

        self::assertSame($tag, $provider->getTag('apple'));
    }

    public function testGetTagThrowsException(): void
    {
        $providerMock = $this->getProviderMock();
        $provider = $this->getProviderUnderTest($providerMock);

        $providerMock
            ->expects(self::once())
            ->method('getTag')
            ->with('fig')
            ->willThrowException(new RuntimeException('tag not found'));

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('tag not found');

        $provider->getTag('fig');
    }

    protected function getProviderUnderTest(Provider $mockerProvider): Caching
    {
        return new Caching($mockerProvider);
    }

    /**
     * @return \Netgen\Bundle\IbexaSiteApiBundle\NamedObject\Provider|\PHPUnit\Framework\MockObject\MockObject
     */
    protected function getProviderMock(): MockObject
    {
        return $this->getMockBuilder(Provider::class)->getMockForAbstractClass();
    }

    protected function getContentMock(): MockObject
    {
        static $contentMock;

        if ($contentMock === null) {
            $contentMock = $this->getMockBuilder(Content::class)->getMock();
        }

        return $contentMock;
    }

    protected function getLocationMock(): MockObject
    {
        static $locationMock;

        if ($locationMock === null) {
            $locationMock = $this->getMockBuilder(Location::class)->getMock();
        }

        return $locationMock;
    }
}
