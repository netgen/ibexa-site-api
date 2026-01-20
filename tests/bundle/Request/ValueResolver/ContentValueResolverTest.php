<?php

declare(strict_types=1);

namespace Netgen\Bundle\IbexaSiteApiBundle\Tests\Request\ValueResolver;

use Netgen\Bundle\IbexaSiteApiBundle\Request\ValueResolver\ContentValueResolver;
use Netgen\IbexaSiteApi\API\Values\Content;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use stdClass;
use Symfony\Component\HttpFoundation\Request;

#[AllowMockObjectsWithoutExpectations]
final class ContentValueResolverTest extends SiteValueResolverTestCase
{
    protected function createResolver(): ContentValueResolver
    {
        return new ContentValueResolver($this->loadService);
    }

    public function testUnsupportedArgumentType(): void
    {
        $request = new Request();
        $argument = $this->createArgumentMetadata(stdClass::class);

        $result = $this->resolver->resolve($request, $argument);

        self::assertSame([], iterator_to_array($result));
    }

    public function testMissingRouteAttribute(): void
    {
        $request = new Request();
        $argument = $this->createArgumentMetadata(Content::class);

        $result = $this->resolver->resolve($request, $argument);

        self::assertSame([], iterator_to_array($result));
    }

    public function testLoadsContentFromRouteAttribute(): void
    {
        $content = $this->createMock(Content::class);

        $this->loadService
            ->expects($this->once())
            ->method('loadContent')
            ->with(42)
            ->willReturn($content);

        $request = new Request([], [], ['contentId' => 42]);
        $argument = $this->createArgumentMetadata(Content::class);

        $result = $this->resolver->resolve($request, $argument);

        self::assertSame([$content], iterator_to_array($result));
    }

    public function testNullableArgumentReturnsNullForInvalidId(): void
    {
        $request = new Request([], [], ['contentId' => 0]);
        $argument = $this->createArgumentMetadata(Content::class, nullable: true);

        $result = $this->resolver->resolve($request, $argument);

        self::assertSame([null], iterator_to_array($result));
    }

    public function testDefaultValueIsUsed(): void
    {
        $default = $this->createMock(Content::class);

        $request = new Request([], [], ['contentId' => 0]);
        $argument = $this->createArgumentMetadata(
            Content::class,
            hasDefault: true,
            defaultValue: $default,
        );

        $result = $this->resolver->resolve($request, $argument);

        self::assertSame([$default], iterator_to_array($result));
    }
}
