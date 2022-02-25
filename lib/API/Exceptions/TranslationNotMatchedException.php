<?php

declare(strict_types=1);

namespace Netgen\IbexaSiteApi\API\Exceptions;

use Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException;

/**
 * This exception is thrown if the translation language could not be resolved.
 */
abstract class TranslationNotMatchedException extends NotFoundException
{
}
