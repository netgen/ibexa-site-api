<?php

declare(strict_types=1);

namespace Netgen\Bundle\IbexaSiteApiBundle\QueryType;

use Ibexa\Contracts\Core\SiteAccess\ConfigResolverInterface;
use Netgen\Bundle\IbexaSiteApiBundle\NamedObject\Provider;
use Netgen\Bundle\IbexaSiteApiBundle\Traits\LanguageExpressionEvaluatorTrait;
use Netgen\Bundle\IbexaSiteApiBundle\View\ContentView;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * ParameterProcessor processes query configuration parameter values using ExpressionLanguage.
 *
 * @internal do not depend on this service, it can be changed without warning
 */
final class ParameterProcessor
{
    use LanguageExpressionEvaluatorTrait;

    public function __construct(
        private readonly ExpressionLanguage $expressionLanguage,
        private readonly RequestStack $requestStack,
        private readonly ConfigResolverInterface $configResolver,
        private readonly Provider $namedObjectProvider,
    ) {}

    /**
     * Return given $value processed with ExpressionLanguage if needed.
     *
     * Parameter $view is used to provide values for evaluation.
     */
    public function process(mixed $value, ContentView $view): mixed
    {
        return $this->evaluate(
            $value,
            $this->expressionLanguage,
            [
                'view' => $view,
                'location' => $view->getSiteLocation(),
                'content' => $view->getSiteContent(),
                'request' => $this->requestStack->getCurrentRequest(),
                'configResolver' => $this->configResolver,
                'namedObject' => $this->namedObjectProvider,
            ],
        );
    }
}
