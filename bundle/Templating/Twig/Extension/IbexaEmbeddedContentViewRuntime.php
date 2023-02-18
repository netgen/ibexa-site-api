<?php

declare(strict_types=1);

namespace Netgen\Bundle\IbexaSiteApiBundle\Templating\Twig\Extension;

use Netgen\Bundle\IbexaSiteApiBundle\View\ContentRenderer;

/**
 * Twig extension runtime for Ibexa CMS embedded content view rendering.
 */
class IbexaEmbeddedContentViewRuntime
{
    private ContentRenderer $contentRenderer;

    public function __construct(ContentRenderer $contentRenderer)
    {
        $this->contentRenderer = $contentRenderer;
    }

    /**
     * Renders the HTML for a given $content.
     */
    public function renderEmbeddedContentView(string $viewType, array $parameters = []): string
    {
        return $this->contentRenderer->renderIbexaEmbeddedContent($viewType, $parameters);
    }
}
