<?php

declare(strict_types=1);

namespace Netgen\Bundle\IbexaSiteApiBundle\Exception;

use Exception;

final class InvalidRedirectConfiguration extends Exception
{
    public function __construct(string $target, ?Exception $previous = null)
    {
        $message = "Could not resolve redirect from the given target: '{$target}'";

        parent::__construct($message, 0, $previous);
    }
}
