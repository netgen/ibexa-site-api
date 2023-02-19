<?php

declare(strict_types=1);

namespace Netgen\Bundle\IbexaSiteApiBundle\View\Provider;

use Ibexa\Bundle\Core\View\Provider\Configured as CoreConfigured;
use Ibexa\Core\MVC\Symfony\Matcher\MatcherFactoryInterface;
use Ibexa\Core\MVC\Symfony\View\View;

final class CoreOverride extends CoreConfigured
{
    private ContentViewFallbackResolver $contentViewFallbackResolver;

    public function __construct(
        MatcherFactoryInterface $matcherFactory,
        ContentViewFallbackResolver $contentViewFallbackResolver,
    ) {
        parent::__construct($matcherFactory);

        $this->contentViewFallbackResolver = $contentViewFallbackResolver;
    }

    public function getView(View $view): ?View
    {
        // Service is dispatched by the configured view class, so this should be safe
        /** @var \Ibexa\Core\MVC\Symfony\View\ContentView $view */
        $configHash = $this->matcherFactory->match($view);

        if ($configHash === null) {
            return $this->contentViewFallbackResolver->getSiteApiFallbackDto($view);
        }

        return $this->buildContentView($configHash);
    }
}
