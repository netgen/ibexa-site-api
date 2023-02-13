<?php

declare(strict_types=1);

namespace Netgen\Bundle\IbexaSiteApiBundle\View;

use Ibexa\Core\MVC\Symfony\View\Renderer;
use Ibexa\Core\MVC\Symfony\View\View;
use LogicException;
use Netgen\Bundle\IbexaSiteApiBundle\Event\RenderViewEvent;
use Netgen\Bundle\IbexaSiteApiBundle\Events;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Controller\ArgumentResolverInterface;
use Symfony\Component\HttpKernel\Controller\ControllerReference;
use Symfony\Component\HttpKernel\Controller\ControllerResolverInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use function call_user_func_array;
use function sprintf;

/**
 * @internal
 *
 * Renders View object using any controller without executing a subrequest
 *
 * @see \Ibexa\Core\MVC\Symfony\View\View
 */
final class ViewRenderer
{
    private RequestStack $requestStack;
    private ControllerResolverInterface $controllerResolver;
    private ArgumentResolverInterface $argumentResolver;
    private Renderer $coreViewRenderer;
    private EventDispatcherInterface $eventDispatcher;

    public function __construct(
        RequestStack $requestStack,
        ControllerResolverInterface $controllerResolver,
        ArgumentResolverInterface $argumentResolver,
        Renderer $coreViewRenderer,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->requestStack = $requestStack;
        $this->controllerResolver = $controllerResolver;
        $this->argumentResolver = $argumentResolver;
        $this->coreViewRenderer = $coreViewRenderer;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function render(View $view, array $parameters, bool $layout): string
    {
        $renderedContent = $this->doRender($view, $parameters, $layout);

        $this->eventDispatcher->dispatch(new RenderViewEvent($view), Events::RENDER_VIEW);

        return $renderedContent;
    }

    private function doRender(View $view, array $parameters, bool $layout): string
    {
        $controllerReference = $view->getControllerReference();

        if ($controllerReference === null) {
            return $this->coreViewRenderer->render($view);
        }

        $parameters['layout'] = $layout;

        return $this->renderController($view, $controllerReference, $parameters);
    }

    private function renderController(View $view, ControllerReference $controllerReference, array $arguments): string
    {
        $controller = $this->resolveController($controllerReference);
        $arguments = $this->resolveControllerArguments($view, $controller, $arguments);

        $result = call_user_func_array($controller, $arguments);

        if ($result instanceof View) {
            return $this->coreViewRenderer->render($result);
        }

        if ($result instanceof Response) {
            return (string) $result->getContent();
        }

        throw new LogicException('Controller result must be ContentView or Response instance');
    }

    private function resolveController(ControllerReference $controllerReference): callable
    {
        $controllerRequest = new Request();
        $controllerRequest->attributes->set('_controller', $controllerReference->controller);
        $controller = $this->controllerResolver->getController($controllerRequest);

        if ($controller === false) {
            throw new NotFoundHttpException(
                sprintf('Unable to find the controller "%s".', $controllerReference->controller),
            );
        }

        return $controller;
    }

    private function resolveControllerArguments(View $view, callable $controller, array $arguments): array
    {
        $masterRequest = $this->requestStack->getMainRequest();

        if ($masterRequest === null) {
            throw new LogicException('A Request must be available.');
        }

        $masterRequest = $masterRequest->duplicate();
        $masterRequest->attributes->set('view', $view);
        $masterRequest->attributes->add($view->getParameters());
        $masterRequest->attributes->add($arguments);

        return $this->argumentResolver->getArguments($masterRequest, $controller);
    }
}
