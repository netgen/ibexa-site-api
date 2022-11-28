<?php

declare(strict_types=1);

namespace Netgen\Bundle\IbexaSiteApiBundle\Templating\Twig\Extension;

use Ibexa\Contracts\Core\Repository\Values\ValueObject;
use Netgen\Bundle\IbexaSiteApiBundle\View\ContentRenderer;

/**
 * Twig extension runtime for Ibexa CMS content view rendering.
 */
final class IbexaContentViewRuntime
{
    private ContentRenderer $contentRenderer;

    public function __construct(ContentRenderer $contentRenderer)
    {
        $this->contentRenderer = $contentRenderer;
    }

    /**
     * Renders the HTML for a given $content.
     *
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException
     */
    public function renderContentView(
        ValueObject $value,
        string $viewType,
        array $parameters = [],
        bool $layout = false
    ): string {
        return $this->contentRenderer->renderIbexaContent($value, $viewType, $parameters, $layout);
    }
}
