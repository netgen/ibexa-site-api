<?php

declare(strict_types=1);

namespace Netgen\Bundle\IbexaSiteApiBundle\Controller;

use Ibexa\Contracts\Core\Repository\Values\Content\Content;
use Ibexa\Contracts\Core\Repository\Values\Content\Location;
use Ibexa\Contracts\Core\SiteAccess\ConfigResolverInterface;
use Ibexa\Core\MVC\Symfony\Controller\Content\PreviewController as BasePreviewController;
use Ibexa\Core\MVC\Symfony\SiteAccess;
use Ibexa\Core\MVC\Symfony\View\ViewManagerInterface;
use Netgen\Bundle\IbexaSiteApiBundle\Routing\UrlAliasRouter;
use Netgen\IbexaSiteApi\Core\Site\Site;
use Symfony\Component\HttpFoundation\Request;

class PreviewController extends BasePreviewController
{
    protected ConfigResolverInterface $configResolver;
    protected Site $site;

    public function setConfigResolver(ConfigResolverInterface $configResolver): void
    {
        $this->configResolver = $configResolver;
    }

    public function setSite(Site $site): void
    {
        $this->site = $site;
    }

    protected function getForwardRequest(
        Location $location,
        Content $content,
        SiteAccess $previewSiteAccess,
        Request $request,
        string $language,
        string $viewType = ViewManagerInterface::VIEW_TYPE_FULL
    ): Request {
        $request = parent::getForwardRequest($location, $content, $previewSiteAccess, $request, $language, $viewType);

        $this->injectAttributes($request, $previewSiteAccess, $language);

        return $request;
    }

    protected function injectAttributes(Request $request, SiteAccess $previewSiteAccess, string $languageCode): void
    {
        $overrideViewAction = $this->configResolver->getParameter(
            'ng_site_api.site_api_is_primary_content_view',
            null,
            $previewSiteAccess->name,
        );

        /** @var bool $overrideViewAction */
        if ($overrideViewAction) {
            $request->attributes->set('_controller', UrlAliasRouter::OVERRIDE_VIEW_ACTION);

            $this->injectSiteApiValueObjects($request, $languageCode);
        }
    }

    /**
     * Injects the Site API value objects into request, replacing the original
     * Ibexa API value objects.
     */
    protected function injectSiteApiValueObjects(Request $request, string $languageCode): void
    {
        $content = $request->attributes->get('content');
        $location = $request->attributes->get('location');

        /** @var \Ibexa\Contracts\Core\Repository\Values\Content\Content $content */
        /** @var \Ibexa\Contracts\Core\Repository\Values\Content\Location $location */
        $siteContent = $this->site->getLoadService()->loadContentForPreview(
            $content->id,
            $content->versionInfo->versionNo,
            $languageCode,
        );
        $siteLocation = $this->site->getDomainObjectMapper()->mapLocation(
            $location,
            $content->versionInfo,
            $languageCode,
        );

        $requestParams = $request->attributes->get('params');
        $requestParams['content'] = $siteContent;
        $requestParams['location'] = $siteLocation;

        $request->attributes->set('content', $siteContent);
        $request->attributes->set('location', $siteLocation);
        $request->attributes->set('params', $requestParams);
    }
}
