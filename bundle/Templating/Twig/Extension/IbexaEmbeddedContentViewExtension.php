<?php

declare(strict_types=1);

namespace Netgen\Bundle\IbexaSiteApiBundle\Templating\Twig\Extension;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * Twig extension for Ibexa CMS embedded content view rendering.
 */
class IbexaEmbeddedContentViewExtension extends AbstractExtension
{
    /**
     * @return \Twig\TwigFunction[]
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction(
                'ng_ibexa_view_content_embedded',
                [IbexaEmbeddedContentViewRuntime::class, 'renderEmbeddedContentView'],
                ['is_safe' => ['html']]
            ),
        ];
    }
}
