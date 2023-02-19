<?php

declare(strict_types=1);

namespace Netgen\Bundle\IbexaSiteApiBundle\View\Provider;

use Ibexa\Contracts\Core\SiteAccess\ConfigResolverInterface;
use Ibexa\Core\MVC\Symfony\View\ContentView;
use Netgen\Bundle\IbexaSiteApiBundle\View\ContentView as SiteContentView;

final class ContentViewFallbackResolver
{
    public function __construct(
        private readonly ConfigResolverInterface $configResolver,
        private readonly string $toIbexaPlatformEmbedFallbackTemplate,
        private readonly string $toIbexaPlatformViewFallbackTemplate,
        private readonly string $toSiteApiEmbedFallbackTemplate,
        private readonly string $toSiteApiViewFallbackTemplate
    ) {
    }

    public function getIbexaPlatformFallbackDto(SiteContentView $view): ?ContentView
    {
        if (!$this->isIbexaPlatformFallbackEnabled()) {
            return null;
        }

        if ($view->isEmbed()) {
            return new ContentView($this->toIbexaPlatformEmbedFallbackTemplate);
        }

        return new ContentView($this->toIbexaPlatformViewFallbackTemplate);
    }

    public function getSiteApiFallbackDto(ContentView $view): ?ContentView
    {
        if (!$this->isSiteApiFallbackEnabled()) {
            return null;
        }

        if ($view->isEmbed()) {
            return new ContentView($this->toSiteApiEmbedFallbackTemplate);
        }

        return new ContentView($this->toSiteApiViewFallbackTemplate);
    }

    private function isIbexaPlatformFallbackEnabled(): bool
    {
        return $this->isSiteApiContentViewEnabled() && $this->useContentViewFallback();
    }

    private function isSiteApiFallbackEnabled(): bool
    {
        return !$this->isSiteApiContentViewEnabled() && $this->useContentViewFallback();
    }

    private function isSiteApiContentViewEnabled(): bool
    {
        return $this->configResolver->getParameter('ng_site_api.site_api_is_primary_content_view');
    }

    private function useContentViewFallback(): bool
    {
        return $this->configResolver->getParameter('ng_site_api.fallback_to_secondary_content_view');
    }
}
