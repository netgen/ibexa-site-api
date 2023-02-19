<?php

declare(strict_types=1);

namespace Netgen\Bundle\IbexaSiteApiBundle\View;

use Ibexa\Contracts\Core\Repository\Values\Content\Content as APIContent;
use Ibexa\Contracts\Core\Repository\Values\Content\Location as APILocation;
use Ibexa\Contracts\Core\Repository\Values\ValueObject;
use LogicException;
use Netgen\Bundle\IbexaSiteApiBundle\View\Builder\ContentViewBuilder;
use Netgen\IbexaSiteApi\API\Values\Content;
use Netgen\IbexaSiteApi\API\Values\Location;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Throwable;

/**
 * ContentRenderer renders a configured content view for the given Content or Location object,
 * without using a subrequest.
 *
 * @uses \Netgen\Bundle\IbexaSiteApiBundle\View\ViewRenderer
 */
final class ContentRenderer
{
    public function __construct(
        private readonly ContentViewBuilder $viewBuilder,
        private readonly ViewRenderer $viewRenderer,
        private readonly LoggerInterface $logger = new NullLogger(),
    ) {
    }

    /**
     * Renders the HTML for a given $content.
     */
    public function renderContent(
        ValueObject $value,
        string $viewType,
        array $parameters = [],
        bool $layout = false,
    ): string {
        /** @var APIContent|Content $content */
        $content = $this->getContent($value);
        $location = $this->getLocation($value);

        $baseParameters = [
            'content' => $content,
            'viewType' => $viewType,
            'layout' => $layout,
            '_controller' => 'ng_content::viewAction',
        ];

        if ($location !== null) {
            $baseParameters['location'] = $location;
        }

        $view = $this->viewBuilder->buildView($baseParameters + $parameters);

        return $this->viewRenderer->render($view, $parameters, $layout);
    }

    /**
     * Renders the HTML for a given $content.
     */
    public function renderEmbeddedContent(string $viewType, array $parameters = []): string
    {
        $baseParameters = [
            'viewType' => $viewType,
            'layout' => false,
            '_controller' => 'ng_content:embedAction',
        ];

        try {
            $view = $this->viewBuilder->buildView($baseParameters + $parameters);
        } catch (Throwable $throwable) {
            $this->logger->error('Could not build the embedded view: ' . $throwable->getMessage());

            return '';
        }

        return $this->viewRenderer->render($view, $parameters, false);
    }

    /**
     * Renders the HTML for a given $content.
     */
    public function renderIbexaContent(
        ValueObject $value,
        string $viewType,
        array $parameters = [],
        bool $layout = false,
    ): string {
        $content = $this->getIbexaContent($value);
        $location = $this->getIbexaLocation($value);

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

    /**
     * Renders the HTML for a given $content.
     */
    public function renderIbexaEmbeddedContent(string $viewType, array $parameters = []): string
    {
        $baseParameters = [
            'viewType' => $viewType,
            'layout' => false,
            '_controller' => 'ibexa_content:embedAction',
        ];

        $view = $this->viewBuilder->buildView($baseParameters + $parameters);

        return $this->viewRenderer->render($view, $parameters, false);
    }

    private function getContent(ValueObject $value): ValueObject
    {
        if ($value instanceof Content || $value instanceof APIContent) {
            return $value;
        }

        if ($value instanceof Location || $value instanceof APILocation) {
            // Ibexa also has a lazy loaded "content" property
            return $value->content;
        }

        throw new LogicException('Given value must be Content or Location instance');
    }

    private function getLocation(ValueObject $value): ?ValueObject
    {
        if ($value instanceof Location || $value instanceof APILocation) {
            return $value;
        }

        return null;
    }

    private function getIbexaContent(ValueObject $value): APIContent
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

    private function getIbexaLocation(ValueObject $value): ?ValueObject
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
