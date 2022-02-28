<?php

declare(strict_types=1);

namespace Netgen\Bundle\IbexaSiteApiBundle\Templating\Twig\Extension;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * Twig extension for Ibexa CMS content view rendering.
 */
class IbexaContentViewExtension extends AbstractExtension
{
    /**
     * @return \Twig\TwigFunction[]
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction(
                'ng_ibexa_view_content',
                [IbexaContentViewRuntime::class, 'renderContentView'],
                ['is_safe' => ['html']],
            ),
        ];
    }
}
