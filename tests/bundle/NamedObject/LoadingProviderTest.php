<?php

declare(strict_types=1);

namespace Netgen\Bundle\IbexaSiteApiBundle\Tests\NamedObject;

use Ibexa\Contracts\Core\Repository\PermissionResolver;
use Ibexa\Contracts\Core\SiteAccess\ConfigResolverInterface;
use Ibexa\Core\MVC\Symfony\ExpressionLanguage\ExpressionLanguage;
use InvalidArgumentException;
use Netgen\Bundle\IbexaSiteApiBundle\NamedObject\ExpressionFunctionProvider;
use Netgen\Bundle\IbexaSiteApiBundle\NamedObject\ParameterProcessor;
use Netgen\Bundle\IbexaSiteApiBundle\NamedObject\Provider\Loading;
use Netgen\IbexaSiteApi\API\LoadService;
use Netgen\IbexaSiteApi\API\Values\Content;
use Netgen\IbexaSiteApi\API\Values\Location;
use Netgen\TagsBundle\API\Repository\TagsService;
use Netgen\TagsBundle\API\Repository\Values\Tags\Tag;
use OutOfBoundsException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/**
 * @internal
 */
final class LoadingProviderTest extends TestCase
{
    public function testHasContentReturnsTrue(): void
    {
        $provider = $this->getProviderUnderTest();

        self::assertTrue($provider->hasContent('apple'));
    }

    public function testHasContentReturnsFalse(): void
    {
        $provider = $this->getProviderUnderTest();

        self::assertFalse($provider->hasContent('quince'));
    }

    public function testGetContent(): void
    {
        $provider = $this->getProviderUnderTest();

        $content = $provider->getContent('apple');

        self::assertSame($this->getContentMock(), $content);
    }

    public function testGetContentByExpression(): void
    {
        $provider = $this->getProviderUnderTest();

        $content = $provider->getContent('plume');

        self::assertSame($this->getContentMock(), $content);
    }

    public function testGetContentThrowsBackendException(): void
    {
        $provider = $this->getProviderUnderTest();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('content backend throws up');

        $provider->getContent('plum');
    }

    public function testGetContentThrowsOutOfBoundsException(): void
    {
        $provider = $this->getProviderUnderTest();

        $this->expectException(OutOfBoundsException::class);
        $this->expectExceptionMessage('Named Content "broom" is not configured');

        $provider->getContent('broom');
    }

    public function testGetContentThrowsInvalidArgumentException(): void
    {
        $provider = $this->getProviderUnderTest();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Named Content "zoom" ID is not string or integer');

        $provider->getContent('zoom');
    }

    public function testGetContentByExpressionThrowsInvalidArgumentException(): void
    {
        $provider = $this->getProviderUnderTest();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Named Content "grapple" ID is not string or integer');

        $provider->getContent('grapple');
    }

    public function testGetContentThroughRemoteId(): void
    {
        $provider = $this->getProviderUnderTest();

        $content = $provider->getContent('pear');

        self::assertSame($this->getContentMock(), $content);
    }

    public function testGetContentThroughRemoteIdByExpression(): void
    {
        $provider = $this->getProviderUnderTest();

        $content = $provider->getContent('wig');

        self::assertSame($this->getContentMock(), $content);
    }

    public function testGetContentThroughRemoteIdThrowsBackendException(): void
    {
        $provider = $this->getProviderUnderTest();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('content backend throws up');

        $provider->getContent('fig');
    }

    public function testHasLocationReturnsTrue(): void
    {
        $provider = $this->getProviderUnderTest();

        self::assertTrue($provider->hasLocation('apple'));
    }

    public function testHasLocationReturnsFalse(): void
    {
        $provider = $this->getProviderUnderTest();

        self::assertFalse($provider->hasLocation('quince'));
    }

    public function testGetLocation(): void
    {
        $provider = $this->getProviderUnderTest();

        $location = $provider->getLocation('apple');

        self::assertSame($this->getLocationMock(), $location);
    }

    public function testGetLocationByExpression(): void
    {
        $provider = $this->getProviderUnderTest();

        $location = $provider->getLocation('plume');

        self::assertSame($this->getLocationMock(), $location);
    }

    public function testGetLocationThrowsBackendException(): void
    {
        $provider = $this->getProviderUnderTest();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('location backend throws up');

        $provider->getLocation('plum');
    }

    public function testGetLocationThrowsOutOfBoundsException(): void
    {
        $provider = $this->getProviderUnderTest();

        $this->expectException(OutOfBoundsException::class);
        $this->expectExceptionMessage('Named Location "broom" is not configured');

        $provider->getLocation('broom');
    }

    public function testGetLocationThrowsInvalidArgumentException(): void
    {
        $provider = $this->getProviderUnderTest();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Named Location "zoom" ID is not string or integer');

        $provider->getLocation('zoom');
    }

    public function testGetLocationByExpressionThrowsInvalidArgumentException(): void
    {
        $provider = $this->getProviderUnderTest();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Named Location "grapple" ID is not string or integer');

        $provider->getLocation('grapple');
    }

    public function testGetLocationThroughRemoteId(): void
    {
        $provider = $this->getProviderUnderTest();

        $location = $provider->getLocation('pear');

        self::assertSame($this->getLocationMock(), $location);
    }

    public function testGetLocationThroughRemoteIdByExpression(): void
    {
        $provider = $this->getProviderUnderTest();

        $location = $provider->getLocation('wig');

        self::assertSame($this->getLocationMock(), $location);
    }

    public function testGetLocationThroughRemoteIdThrowsBackendException(): void
    {
        $provider = $this->getProviderUnderTest();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('location backend throws up');

        $provider->getLocation('fig');
    }

    public function testHasTagReturnsTrue(): void
    {
        $provider = $this->getProviderUnderTest();

        self::assertTrue($provider->hasContent('apple'));
    }

    public function testHasTagReturnsFalse(): void
    {
        $provider = $this->getProviderUnderTest();

        self::assertFalse($provider->hasContent('quince'));
    }

    public function testGetTag(): void
    {
        $provider = $this->getProviderUnderTest();

        $tag = $provider->getTag('apple');

        self::assertSame(42, $tag->id);
    }

    public function testGetTagByExpression(): void
    {
        $provider = $this->getProviderUnderTest();

        $tag = $provider->getTag('plume');

        self::assertSame(42, $tag->id);
    }

    public function testGetTagThrowsBackendException(): void
    {
        $provider = $this->getProviderUnderTest();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('tag backend throws up');

        $provider->getTag('plum');
    }

    public function testGetTagThrowsOutOfBoundsException(): void
    {
        $provider = $this->getProviderUnderTest();

        $this->expectException(OutOfBoundsException::class);
        $this->expectExceptionMessage('Named Tag "broom" is not configured');

        $provider->getTag('broom');
    }

    public function testGetTagThrowsInvalidArgumentException(): void
    {
        $provider = $this->getProviderUnderTest();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Named Tag "zoom" ID is not string or integer');

        $provider->getTag('zoom');
    }

    public function testGetTagByExpressionThrowsInvalidArgumentException(): void
    {
        $provider = $this->getProviderUnderTest();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Named Tag "grapple" ID is not string or integer');

        $provider->getTag('grapple');
    }

    public function testGetTagThroughRemoteId(): void
    {
        $provider = $this->getProviderUnderTest();

        $tag = $provider->getTag('pear');

        self::assertSame('abc', $tag->remoteId);
    }

    public function testGetTagThroughRemoteIdByExpression(): void
    {
        $provider = $this->getProviderUnderTest();

        $tag = $provider->getTag('wig');

        self::assertSame('abc', $tag->remoteId);
    }

    public function testGetTagThroughRemoteIdThrowsBackendException(): void
    {
        $provider = $this->getProviderUnderTest();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('tag backend throws up');

        $provider->getTag('fig');
    }

    protected function getProviderUnderTest(): Loading
    {
        $configResolver = $this->getConfigResolverMock();

        return new Loading(
            $this->getLoadServiceMock(),
            $this->getTagServiceMock(),
            $this->getParameterProcessor($configResolver),
            $configResolver,
        );
    }

    protected function getLoadServiceMock(): LoadService
    {
        $mock = $this->getMockBuilder(LoadService::class)->getMock();

        $mock
            ->method('loadContent')
            ->willReturnCallback(function ($id) {
                if ($id === 42) {
                    return $this->getContentMock();
                }

                throw new RuntimeException('content backend throws up');
            });

        $mock
            ->method('loadContentByRemoteId')
            ->willReturnCallback(function ($id) {
                if ($id === 'abc') {
                    return $this->getContentMock();
                }

                throw new RuntimeException('content backend throws up');
            });

        $mock
            ->method('loadLocation')
            ->willReturnCallback(function ($id) {
                if ($id === 42) {
                    return $this->getLocationMock();
                }

                throw new RuntimeException('location backend throws up');
            });

        $mock
            ->method('loadLocationByRemoteId')
            ->willReturnCallback(function ($id) {
                if ($id === 'abc') {
                    return $this->getLocationMock();
                }

                throw new RuntimeException('location backend throws up');
            });

        return $mock;
    }

    protected function getTagServiceMock(): TagsService
    {
        $mock = $this->getMockBuilder(TagsService::class)->getMock();

        $mock
            ->method('loadTag')
            ->willReturnCallback(static function ($id) {
                if ($id === 42) {
                    return new Tag(['id' => 42]);
                }

                throw new RuntimeException('tag backend throws up');
            });

        $mock
            ->method('loadTagByRemoteId')
            ->willReturnCallback(static function ($id) {
                if ($id === 'abc') {
                    return new Tag(['remoteId' => 'abc']);
                }

                throw new RuntimeException('tag backend throws up');
            });

        return $mock;
    }

    protected function getConfigResolverMock(): ConfigResolverInterface
    {
        $configResolverMock = $this->getMockBuilder(ConfigResolverInterface::class)->getMock();

        $template = [
            'apple' => 42,
            'pear' => 'abc',
            'plum' => 24,
            'fig' => 'cba',
            'zoom' => true,
            'plume' => "@=config('one', 'namespace', 'scope')",
            'wig' => "@=config('two', 'namespace', 'scope')",
            'grapple' => "@=config('four')",
        ];

        $getParameterMap = [
            [
                'ng_site_api.named_objects',
                null,
                null,
                [
                    'content' => $template,
                    'locations' => $template,
                    'tags' => $template,
                ],
            ],
            ['one', 'namespace', 'scope', 42],
            ['two', 'namespace', 'scope', 'abc'],
            ['four', null, null, true],
        ];

        $configResolverMock
            ->method('getParameter')
            ->willReturnMap($getParameterMap);

        return $configResolverMock;
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

    protected function getParameterProcessor(ConfigResolverInterface $configResolver): ParameterProcessor
    {
        $expressionLanguage = new ExpressionLanguage(null, [new ExpressionFunctionProvider()]);
        $permissionResolver = $this->getMockBuilder(PermissionResolver::class)->getMock();

        return new ParameterProcessor(
            $expressionLanguage,
            $configResolver,
            $permissionResolver,
        );
    }
}
