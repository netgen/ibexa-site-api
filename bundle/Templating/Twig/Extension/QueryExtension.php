<?php

declare(strict_types=1);

namespace Netgen\Bundle\IbexaSiteApiBundle\Templating\Twig\Extension;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * Twig extension for executing queries from the QueryDefinitionCollection injected
 * into the template.
 */
class QueryExtension extends AbstractExtension
{
    /**
     * @return \Twig\TwigFunction[]
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction(
                'ng_query',
                [QueryRuntime::class, 'executeQuery'],
                ['needs_context' => true],
            ),
            new TwigFunction(
                'ng_sudo_query',
                [QueryRuntime::class, 'sudoExecuteQuery'],
                ['needs_context' => true],
            ),
            new TwigFunction(
                'ng_raw_query',
                [QueryRuntime::class, 'executeRawQuery'],
                ['needs_context' => true],
            ),
            new TwigFunction(
                'ng_sudo_raw_query',
                [QueryRuntime::class, 'sudoExecuteRawQuery'],
                ['needs_context' => true],
            ),
        ];
    }
}
