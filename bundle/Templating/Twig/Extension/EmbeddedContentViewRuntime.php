<?php

declare(strict_types=1);

namespace Netgen\Bundle\IbexaSiteApiBundle\Templating\Twig\Extension;

use Netgen\Bundle\IbexaSiteApiBundle\View\Builder\ContentViewBuilder;
use Netgen\Bundle\IbexaSiteApiBundle\View\ViewRenderer;

/**
 * Twig extension runtime for Site API embedded content view rendering.
 */
class EmbeddedContentViewRuntime
{
    private ContentViewBuilder $viewBuilder;
    private ViewRenderer $viewRenderer;

    public function __construct(
        ContentViewBuilder $viewBuilder,
        ViewRenderer $viewRenderer
    ) {
        $this->viewBuilder = $viewBuilder;
        $this->viewRenderer = $viewRenderer;
    }

    /**
     * Renders the HTML for a given $content.
     *
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException
     */
    public function renderEmbeddedContentView(string $viewType, array $parameters = []): string
    {
        $baseParameters = [
            'viewType' => $viewType,
            'layout' => false,
            '_controller' => 'ng_content:embedAction',
        ];

        $view = $this->viewBuilder->buildView($baseParameters + $parameters);

        return $this->viewRenderer->render($view, $parameters, false);
    }
}
