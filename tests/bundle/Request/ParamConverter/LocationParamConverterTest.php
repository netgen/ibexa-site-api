<?php

declare(strict_types=1);

namespace Netgen\Bundle\IbexaSiteApiBundle\Tests\Request\ParamConverter;

use Netgen\Bundle\IbexaSiteApiBundle\Request\ParamConverter\LocationParamConverter;
use Netgen\IbexaSiteApi\API\LoadService;
use Netgen\IbexaSiteApi\API\Values\Location;
use Symfony\Component\HttpFoundation\Request;

/**
 * @internal
 */
final class LocationParamConverterTest extends AbstractParamConverterTest
{
    public const PROPERTY_NAME = 'locationId';
    public const LOCATION_CLASS = Location::class;

    /**
     * @var \Netgen\Bundle\IbexaSiteApiBundle\Request\ParamConverter\LocationParamConverter
     */
    protected $converter;

    protected function setUp(): void
    {
        $this->loadServiceMock = $this->createMock(LoadService::class);
        $this->converter = new LocationParamConverter($this->loadServiceMock);
    }

    public function testSupports(): void
    {
        $config = $this->createConfiguration(self::LOCATION_CLASS);
        self::assertTrue($this->converter->supports($config));
    }

    public function testDoesNotSupport(): void
    {
        $config = $this->createConfiguration(__CLASS__);
        self::assertFalse($this->converter->supports($config));
        $config = $this->createConfiguration();
        self::assertFalse($this->converter->supports($config));
    }

    public function testApplyLocation(): void
    {
        $id = 42;
        $valueObject = $this->createMock(Location::class);
        $this->loadServiceMock
            ->expects(self::once())
            ->method('loadLocation')
            ->with($id)
            ->willReturn($valueObject);

        $request = new Request([], [], [self::PROPERTY_NAME => $id]);

        $config = $this->createConfiguration(self::LOCATION_CLASS, 'location');

        $this->converter->apply($request, $config);

        self::assertInstanceOf(self::LOCATION_CLASS, $request->attributes->get('location'));
    }

    public function testApplyLocationOptionalWithEmptyAttribute(): void
    {
        $request = new Request([], [], [self::PROPERTY_NAME => null]);

        $config = $this->createConfiguration(self::LOCATION_CLASS, 'location');
        $config->expects(self::once())
            ->method('isOptional')
            ->willReturn(true);

        self::assertFalse($this->converter->apply($request, $config));
        self::assertNull($request->attributes->get('location'));
    }
}
