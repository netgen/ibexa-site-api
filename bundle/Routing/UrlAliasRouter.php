<?php

declare(strict_types=1);

namespace Netgen\Bundle\IbexaSiteApiBundle\Routing;

use Ibexa\Bundle\Core\Routing\UrlAliasRouter as BaseUrlAliasRouter;
use Ibexa\Core\MVC\Symfony\Routing\UrlAliasRouter as CoreUrlAliasRouter;
use Symfony\Component\HttpFoundation\Request;

/**
 * Do not use directly; use @router instead to keep the chain routing machanism.
 */
class UrlAliasRouter extends BaseUrlAliasRouter
{
    public const OVERRIDE_VIEW_ACTION = 'ng_content::viewAction';

    public function matchRequest(Request $request): array
    {
        $parameters = parent::matchRequest($request);
        $isSiteApiPrimaryContentView = $this->configResolver->getParameter('ng_site_api.site_api_is_primary_content_view');

        if ($isSiteApiPrimaryContentView && $parameters['_controller'] === CoreUrlAliasRouter::VIEW_ACTION) {
            $parameters['_controller'] = self::OVERRIDE_VIEW_ACTION;
        }

        return $parameters;
    }
}
