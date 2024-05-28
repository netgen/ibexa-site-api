<?php

declare(strict_types=1);

namespace Netgen\IbexaSiteApi\API\Values;

/**
 * Provides debug information for view developers.
 */
interface DebugInfo
{
    /**
     * @return array<string, mixed>
     */
    public function getDebugInfo(): array;
}
