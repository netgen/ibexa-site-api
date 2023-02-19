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
        } catch (InvalidVariationException) {
            $this->logger->error("Couldn't get variation '{$variationName}' for image with id {$value->id}");
        } catch (SourceImageNotFoundException) {
            $this->logger->error(
                "Couldn't create variation '{$variationName}' for image with id {$value->id} because source image can't be found",
            );
        } catch (InvalidArgumentException) {
            $this->logger->error(
                "Couldn't create variation '{$variationName}' for image with id {$value->id} because an image could not be created from the given input",
            );
        }

        return new Variation();
    }
}
