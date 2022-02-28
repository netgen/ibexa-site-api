<?php

declare(strict_types=1);

namespace Netgen\Bundle\IbexaSiteApiBundle\Templating\Twig\Extension;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * Twig extension for Site API content view rendering.
 */
class ContentViewExtension extends AbstractExtension
{
    /**
     * @return \Twig\TwigFunction[]
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction(
                'ng_view_content',
                [ContentViewRuntime::class, 'renderContentView'],
                ['is_safe' => ['html']],
            ),
        ];
    }
}
