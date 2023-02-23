<?php

declare(strict_types=1);

namespace Netgen\Bundle\IbexaSiteApiBundle\Templating\Twig\Extension;

use Ibexa\Contracts\Core\Repository\Exceptions\InvalidVariationException;
use Ibexa\Contracts\Core\Variation\Values\Variation;
use Ibexa\Contracts\Core\Variation\VariationHandler;
use Ibexa\Core\MVC\Exception\SourceImageNotFoundException;
use InvalidArgumentException;
use Netgen\IbexaSiteApi\API\Values\Field;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

use function sprintf;

class ImageRuntime
{
    public function __construct(
        private readonly VariationHandler $imageVariationService,
        private readonly LoggerInterface $logger = new NullLogger(),
    ) {
    }

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

        return new Variation();
    }
}
