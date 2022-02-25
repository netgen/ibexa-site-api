<?php

declare(strict_types=1);

namespace Netgen\Bundle\IbexaSiteApiBundle\Templating\Twig\Extension;

use Ibexa\Core\MVC\Symfony\View\Builder\ContentViewBuilder;
use Netgen\Bundle\IbexaSiteApiBundle\View\ViewRenderer;

/**
 * Twig extension runtime for Ibexa CMS embedded content view rendering.
 */
class IbexaEmbeddedContentViewRuntime
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
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException
     */
    public function renderEmbeddedContentView(string $viewType, array $parameters = []): string
    {
        $baseParameters = [
            'viewType' => $viewType,
            'layout' => false,
            '_controller' => 'ibexa_content:embedAction',
        ];

        $view = $this->viewBuilder->buildView($baseParameters + $parameters);

        return $this->viewRenderer->render($view, $parameters, false);
    }
}
