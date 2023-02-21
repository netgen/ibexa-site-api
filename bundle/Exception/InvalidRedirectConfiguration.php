<?php

declare(strict_types=1);

namespace Netgen\Bundle\IbexaSiteApiBundle\Exception;

use Exception;

final class InvalidRedirectConfiguration extends Exception
{
    public function __construct(string $target, ?Exception $previous = null)
    {
        $message = sprintf("Could not resolve redirect from the given target: '%s'", $target);

        parent::__construct($message, 0, $previous);
    }
}
