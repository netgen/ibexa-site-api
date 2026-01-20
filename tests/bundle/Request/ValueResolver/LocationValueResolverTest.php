<?php

declare(strict_types=1);

namespace Netgen\Bundle\IbexaSiteApiBundle\Tests\Request\ValueResolver;

use Netgen\Bundle\IbexaSiteApiBundle\Request\ValueResolver\LocationValueResolver;
use Netgen\IbexaSiteApi\API\Values\Location;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use stdClass;
use Symfony\Component\HttpFoundation\Request;

#[AllowMockObjectsWithoutExpectations]
final class LocationValueResolverTest extends SiteValueResolverTestCase
{
    protected function createResolver(): LocationValueResolver
    {
        return new LocationValueResolver($this->loadService);
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
        $argument = $this->createArgumentMetadata(Location::class);

        $result = $this->resolver->resolve($request, $argument);

        self::assertSame([], iterator_to_array($result));
    }

    public function testLoadsContentFromRouteAttribute(): void
    {
        $location = $this->createMock(Location::class);

        $this->loadService
            ->expects($this->once())
            ->method('loadLocation')
            ->with(42)
            ->willReturn($location);

        $request = new Request([], [], ['locationId' => 42]);
        $argument = $this->createArgumentMetadata(Location::class);

        $result = $this->resolver->resolve($request, $argument);

        self::assertSame([$location], iterator_to_array($result));
    }

    public function testNullableArgumentReturnsNullForInvalidId(): void
    {
        $request = new Request([], [], ['locationId' => 0]);
        $argument = $this->createArgumentMetadata(Location::class, nullable: true);

        $result = $this->resolver->resolve($request, $argument);

        self::assertSame([null], iterator_to_array($result));
    }

    public function testDefaultValueIsUsed(): void
    {
        $default = $this->createMock(Location::class);

        $request = new Request([], [], ['locationId' => 0]);
        $argument = $this->createArgumentMetadata(
            Location::class,
            hasDefault: true,
            defaultValue: $default,
        );

        $result = $this->resolver->resolve($request, $argument);

        self::assertSame([$default], iterator_to_array($result));
    }
}
