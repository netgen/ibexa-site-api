<?php

declare(strict_types=1);

namespace Netgen\Bundle\IbexaSiteApiBundle\Tests\Templating\Twig\Extension;

use Ibexa\Contracts\Core\Repository\Exceptions\InvalidVariationException;
use Ibexa\Contracts\Core\Repository\Values\Content\Field as RepositoryField;
use Ibexa\Contracts\Core\Repository\Values\Content\VersionInfo;
use Ibexa\Contracts\Core\Variation\Values\Variation;
use Ibexa\Contracts\Core\Variation\VariationHandler;
use Ibexa\Core\FieldType\Image\Value as ImageValue;
use Ibexa\Core\MVC\Exception\SourceImageNotFoundException;
use InvalidArgumentException;
use Netgen\Bundle\IbexaSiteApiBundle\Templating\Twig\Extension\ImageRuntime;
use Netgen\IbexaSiteApi\API\Values\Content;
use Netgen\IbexaSiteApi\Core\Site\Values\Field;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Throwable;

/**
 * @internal
 */
final class ImageRuntimeTest extends TestCase
{
    public function testGetImageVariationReturnsVariation(): void
    {
        $field = $this->getField();
        $variation = new Variation(['uri' => '/var/site/storage/images/image.png']);

        $variationHandler = $this->createMock(VariationHandler::class);
        $variationHandler
            ->expects(self::once())
            ->method('getVariation')
            ->with($field->innerField, $field->content->versionInfo, 'large')
            ->willReturn($variation);

        $runtime = new ImageRuntime($variationHandler);

        self::assertSame($variation, $runtime->getImageVariation($field, 'large'));
    }

    #[DataProvider('provideGetImageVariationReturnsUsableEmptyVariationOnFailureCases')]
    public function testGetImageVariationReturnsUsableEmptyVariationOnFailure(Throwable $exception): void
    {
        $variationHandler = self::createStub(VariationHandler::class);
        $variationHandler->method('getVariation')->willThrowException($exception);

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::once())->method('error');

        $runtime = new ImageRuntime($variationHandler, $logger);
        $variation = $runtime->getImageVariation($this->getField(), 'large');

        // Templates read these properties directly, e.g. ng_image_alias(field, 'large').uri,
        // so none of them may be left uninitialized.
        self::assertSame('', $variation->uri);
        self::assertSame('', $variation->fileName);
        self::assertSame('', $variation->dirPath);
        self::assertSame('', $variation->mimeType);
        self::assertSame(0, $variation->fileSize);
        self::assertNotNull($variation->lastModified);
    }

    public static function provideGetImageVariationReturnsUsableEmptyVariationOnFailureCases(): iterable
    {
        yield 'invalid variation' => [new InvalidVariationException('large', 'image')];

        yield 'source image not found' => [new SourceImageNotFoundException('images/image.png')];

        yield 'invalid argument' => [new InvalidArgumentException('Cannot create an image')];
    }

    private function getField(): Field
    {
        $versionInfo = self::createStub(VersionInfo::class);

        $content = self::createStub(Content::class);
        $content
            ->method('__get')
            ->willReturnCallback(
                static fn (string $property) => $property === 'versionInfo' ? $versionInfo : null,
            );

        return new Field([
            'isEmpty' => false,
            'isSurrogate' => false,
            'id' => 42,
            'fieldDefIdentifier' => 'image',
            'value' => new ImageValue(['id' => 'images/image.png']),
            'content' => $content,
            'innerField' => new RepositoryField(['id' => 42, 'fieldDefIdentifier' => 'image']),
        ]);
    }
}
