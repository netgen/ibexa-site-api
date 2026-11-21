<?php

declare(strict_types=1);

namespace Netgen\Bundle\IbexaSiteApiBundle\Templating\Twig\Extension;

use DateTimeImmutable;
use Ibexa\Contracts\Core\Repository\Exceptions\InvalidVariationException;
use Ibexa\Contracts\Core\Variation\Values\Variation;
use Ibexa\Contracts\Core\Variation\VariationHandler;
use Ibexa\Core\MVC\Exception\SourceImageNotFoundException;
use InvalidArgumentException;
use Netgen\IbexaSiteApi\API\Values\Field;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

use function sprintf;

readonly class ImageRuntime
{
    public function __construct(
        private VariationHandler $imageVariationService,
        private LoggerInterface $logger = new NullLogger(),
    ) {}

    /**
     * Returns the image variation object for $field/$versionInfo.
     */
    public function getImageVariation(Field $field, string $variationName): Variation
    {
        /** @var \Ibexa\Core\FieldType\Image\Value $value */
        $value = $field->value;

        try {
            return $this->imageVariationService->getVariation(
                $field->innerField,
                $field->content->versionInfo,
                $variationName,
            );
        } catch (InvalidVariationException $exception) {
            $this->logger->error(
                sprintf(
                    "Couldn't get variation '%s' for image with id %s: %s",
                    $variationName,
                    $value->id,
                    $exception->getMessage(),
                ),
            );
        } catch (SourceImageNotFoundException $exception) {
            $this->logger->error(
                sprintf(
                    "Couldn't create variation '%s' for image with id %s because source image can't be found: %s",
                    $variationName,
                    $value->id,
                    $exception->getMessage(),
                ),
            );
        } catch (InvalidArgumentException $exception) {
            $this->logger->error(
                sprintf(
                    "Couldn't create variation '%s' for image with id %s because an image could not be created from the given input: %s",
                    $variationName,
                    $value->id,
                    $exception->getMessage(),
                ),
            );
        }

        return $this->createEmptyVariation();
    }

    /**
     * Returns a Variation with all properties initialized to empty values.
     *
     * Variation properties are typed and must not be accessed before initialization,
     * so a bare Variation would make templates like ng_image_alias(field, 'large').uri
     * throw an Error instead of rendering an empty value.
     */
    private function createEmptyVariation(): Variation
    {
        return new Variation([
            'fileSize' => 0,
            'mimeType' => '',
            'fileName' => '',
            'dirPath' => '',
            'uri' => '',
            'lastModified' => new DateTimeImmutable('@0'),
        ]);
    }
}
