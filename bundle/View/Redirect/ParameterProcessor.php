<?php

declare(strict_types=1);

namespace Netgen\Bundle\IbexaSiteApiBundle\View\Redirect;

use Ibexa\Contracts\Core\SiteAccess\ConfigResolverInterface;
use Netgen\Bundle\IbexaSiteApiBundle\NamedObject\Provider;
use Netgen\Bundle\IbexaSiteApiBundle\Traits\LanguageExpressionEvaluatorTrait;
use Netgen\Bundle\IbexaSiteApiBundle\View\ContentView;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

final readonly class ParameterProcessor
{
    use LanguageExpressionEvaluatorTrait;

    public function __construct(
        private ExpressionLanguage $expressionLanguage,
        private ConfigResolverInterface $configResolver,
        private Provider $namedObjectProvider,
    ) {}

    /**
     * Return the given $value processed with ExpressionLanguage if needed.
     *
     * Parameter $view is used to provide values for evaluation.
     */
    public function process(mixed $value, ContentView $view): mixed
    {
        return $this->evaluate(
            $value,
            $this->expressionLanguage,
            [
                'location' => $view->getSiteLocation(),
                'content' => $view->getSiteContent(),
                'configResolver' => $this->configResolver,
                'namedObject' => $this->namedObjectProvider,
            ],
        );
    }
}
