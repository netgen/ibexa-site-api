<?php

declare(strict_types=1);

namespace Netgen\Bundle\IbexaSiteApiBundle\Templating\Twig\Extension;

use Ibexa\Contracts\Core\Repository\Values\ValueObject;
use Netgen\Bundle\IbexaSiteApiBundle\View\ContentRenderer;

/**
 * Twig extension runtime for Site API content view rendering.
 */
class ContentViewRuntime
{
    public function __construct(
        private readonly ContentRenderer $contentRenderer,
    ) {}

    /**
     * Renders the HTML for a given $content.
     */
    public function renderContentView(
        ValueObject $value,
        string $viewType,
        array $parameters = [],
        bool $layout = false,
    ): string {
        return $this->contentRenderer->renderContent($value, $viewType, $parameters, $layout);
    }
}
