<?php

declare(strict_types=1);

namespace Netgen\Bundle\IbexaSiteApiBundle\Templating\Twig\Extension;

use Ibexa\Contracts\Core\Repository\Values\Content\Content as APIContent;
use Ibexa\Contracts\Core\Repository\Values\Content\Location as APILocation;
use Ibexa\Contracts\Core\Repository\Values\ValueObject;
use Ibexa\Core\MVC\Symfony\View\Builder\ContentViewBuilder;
use LogicException;
use Netgen\Bundle\IbexaSiteApiBundle\View\ViewRenderer;
use Netgen\IbexaSiteApi\API\Values\Content;
use Netgen\IbexaSiteApi\API\Values\Location;

/**
 * Twig extension runtime for Ibexa CMS content view rendering.
 */
final class IbexaContentViewRuntime
{
    private ContentViewBuilder $viewBuilder;
    private ViewRenderer $viewRenderer;

    public function __construct(ContentViewBuilder $viewBuilder, ViewRenderer $viewRenderer)
    {
        $this->viewBuilder = $viewBuilder;
        $this->viewRenderer = $viewRenderer;
    }

    /**
     * Renders the HTML for a given $content.
     *
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException
     */
    public function renderContentView(
        ValueObject $value,
        string $viewType,
        array $parameters = [],
        bool $layout = false
    ): string {
        $content = $this->getContent($value);
        $location = $this->getLocation($value);

        $baseParameters = [
            'content' => $content,
            'viewType' => $viewType,
            'layout' => $layout,
            '_controller' => 'ibexa_content::viewAction',
        ];

        if ($location !== null) {
            $baseParameters['location'] = $location;
        }

        $view = $this->viewBuilder->buildView($baseParameters + $parameters);

        return $this->viewRenderer->render($view, $parameters, $layout);
    }

    private function getContent(ValueObject $value): APIContent
    {
        if ($value instanceof Content) {
            return $value->innerContent;
        }

        if ($value instanceof APIContent) {
            return $value;
        }

        if ($value instanceof APILocation) {
            return $value->getContent();
        }

        if ($value instanceof Location) {
            return $value->content->innerContent;
        }

        throw new LogicException('Given value must be Content or Location instance.');
    }

    private function getLocation(ValueObject $value): ?ValueObject
    {
        if ($value instanceof Location) {
            return $value->innerLocation;
        }

        if ($value instanceof APILocation) {
            return $value;
        }

        return null;
    }
}
