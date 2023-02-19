<?php

declare(strict_types=1);

namespace Netgen\Bundle\IbexaSiteApiBundle\View\Provider;

use Ibexa\Bundle\Core\View\Provider\Configured as CoreConfigured;
use Ibexa\Core\MVC\Symfony\Matcher\MatcherFactoryInterface;
use Ibexa\Core\MVC\Symfony\View\View;

final class CoreOverride extends CoreConfigured
{
    public function __construct(
        MatcherFactoryInterface $matcherFactory,
        private readonly ContentViewFallbackResolver $contentViewFallbackResolver,
    ) {
        parent::__construct($matcherFactory);
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
