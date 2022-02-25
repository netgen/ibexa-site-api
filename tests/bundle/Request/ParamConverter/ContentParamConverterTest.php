<?php

declare(strict_types=1);

namespace Netgen\Bundle\IbexaSiteApiBundle\Tests\Request\ParamConverter;

use Netgen\Bundle\IbexaSiteApiBundle\Request\ParamConverter\ContentParamConverter;
use Netgen\IbexaSiteApi\API\LoadService;
use Netgen\IbexaSiteApi\API\Values\Content;
use Symfony\Component\HttpFoundation\Request;

/**
 * @internal
 */
final class ContentParamConverterTest extends AbstractParamConverterTest
{
    const PROPERTY_NAME = 'contentId';
    const CONTENT_CLASS = Content::class;

    /**
     * @var \Netgen\Bundle\IbexaSiteApiBundle\Request\ParamConverter\ContentParamConverter
     */
    protected $converter;

    protected function setUp(): void
    {
        $this->loadServiceMock = $this->createMock(LoadService::class);
        $this->converter = new ContentParamConverter($this->loadServiceMock);
    }

    public function testSupports(): void
    {
        $config = $this->createConfiguration(self::CONTENT_CLASS);
        self::assertTrue($this->converter->supports($config));
    }

    public function testDoesNotSupport(): void
    {
        $config = $this->createConfiguration(__CLASS__);
        self::assertFalse($this->converter->supports($config));
        $config = $this->createConfiguration();
        self::assertFalse($this->converter->supports($config));
    }

    public function testApplyContent(): void
    {
        $id = 42;
        $valueObject = $this->createMock(Content::class);
        $this->loadServiceMock
            ->expects(self::once())
            ->method('loadContent')
            ->with($id)
            ->willReturn($valueObject);

        $request = new Request([], [], [self::PROPERTY_NAME => $id]);

        $config = $this->createConfiguration(self::CONTENT_CLASS, 'content');

        $this->converter->apply($request, $config);

        self::assertInstanceOf(self::CONTENT_CLASS, $request->attributes->get('content'));
    }

    public function testApplyContentOptionalWithEmptyAttribute(): void
    {
        $request = new Request([], [], [self::PROPERTY_NAME => null]);
        $config = $this->createConfiguration(self::CONTENT_CLASS, 'content');
        $config->expects(self::once())
            ->method('isOptional')
            ->willReturn(true);
        self::assertFalse($this->converter->apply($request, $config));
        self::assertNull($request->attributes->get('content'));
    }
}
