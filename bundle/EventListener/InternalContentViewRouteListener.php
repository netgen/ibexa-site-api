<?php

declare(strict_types=1);

namespace Netgen\Bundle\IbexaSiteApiBundle\EventListener;

use Exception;
use Ibexa\Bundle\AdminUi\IbexaAdminUiBundle;
use Ibexa\Contracts\Core\SiteAccess\ConfigResolverInterface;
use Ibexa\Core\MVC\Symfony\Routing\Generator\UrlAliasGenerator;
use Ibexa\Core\MVC\Symfony\Routing\UrlAliasRouter;
use Ibexa\Core\MVC\Symfony\SiteAccess;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Controller\ControllerReference;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Fragment\FragmentHandler;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\RouterInterface;

use function class_exists;
use function in_array;

final class InternalContentViewRouteListener implements EventSubscriberInterface
{
    public function __construct(
        private readonly ConfigResolverInterface $configResolver,
        private readonly FragmentHandler $fragmentHandler,
        private readonly RouterInterface $router,
        private readonly array $siteaccessGroups,
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['onKernelRequest', 0],
        ];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();

        if (!$this->isInternalContentViewRoute($request)) {
            return;
        }

        $siteaccess = $request->attributes->get('siteaccess');

        if (!$siteaccess instanceof SiteAccess || $this->isAdminSiteaccess($siteaccess)) {
            return;
        }

        if (!$this->configResolver->getParameter('ng_site_api.enable_internal_view_route')) {
            throw new NotFoundHttpException(
                'Internal Content view route has been disabled, check your Site API configuration for: "ng_site_api.enable_internal_view_route"',
            );
        }

        $event->setResponse($this->getResponse($request));
    }

    private function getResponse(Request $request): Response
    {
        if ($this->configResolver->getParameter('ng_site_api.redirect_internal_view_route_to_url_alias')) {
            return new RedirectResponse($this->generateUrlAlias($request), 308);
        }

        return new Response($this->renderView($request));
    }

    private function renderView(Request $request): ?string
    {
        $attributes = [
            'contentId' => $request->attributes->getInt('contentId'),
            'layout' => $request->attributes->getBoolean('layout', true),
            'viewType' => 'full',
        ];

        $locationId = $request->attributes->get('locationId');

        if ($locationId !== null) {
            $attributes['locationId'] = (int) $locationId;
        }

        return $this->fragmentHandler->render(
            new ControllerReference('ng_content::viewAction', $attributes),
        );
    }

    private function isInternalContentViewRoute(Request $request): bool
    {
        return $request->attributes->get('_route') === UrlAliasGenerator::INTERNAL_CONTENT_VIEW_ROUTE;
    }

    private function isAdminSiteaccess(SiteAccess $siteaccess): bool
    {
        return in_array(
            $siteaccess->name,
            $this->siteaccessGroups[$this->getAdminSiteaccessGroupName()] ?? [],
            true,
        );
    }

    private function getAdminSiteaccessGroupName(): string
    {
        if (class_exists(IbexaAdminUiBundle::class)) {
            return IbexaAdminUiBundle::ADMIN_GROUP_NAME;
        }

        return 'admin_group';
    }

    private function generateUrlAlias(Request $request): string
    {
        $parameters = [
            'contentId' => $request->attributes->getInt('contentId'),
        ];

        $locationId = $request->attributes->get('locationId');

        if ($locationId !== null) {
            $parameters['locationId'] = (int) $locationId;
        }

        try {
            return $this->router->generate(UrlAliasRouter::URL_ALIAS_ROUTE_NAME, $parameters);
        } catch (Exception $exception) {
            throw new NotFoundHttpException('URL alias could not be generated', $exception);
        }
    }
}
