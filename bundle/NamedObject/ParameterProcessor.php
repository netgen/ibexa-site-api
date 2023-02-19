<?php

declare(strict_types=1);

namespace Netgen\Bundle\IbexaSiteApiBundle\NamedObject;

use Ibexa\Contracts\Core\Repository\PermissionResolver;
use Ibexa\Contracts\Core\SiteAccess\ConfigResolverInterface;
use Netgen\Bundle\IbexaSiteApiBundle\Traits\LanguageExpressionEvaluatorTrait;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

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
        private readonly ConfigResolverInterface $configResolver,
        private readonly PermissionResolver $permissionResolver
    ) {
    }

    /**
     * Return given $value processed with ExpressionLanguage if needed.
     *
     * Parameter $view is used to provide values for evaluation.
     */
    public function process(mixed $value): mixed
    {
        return $this->evaluate(
            $value,
            $this->expressionLanguage,
            [
                'configResolver' => $this->configResolver,
                'currentUserId' => $this->permissionResolver->getCurrentUserReference()->getUserId(),
            ],
        );
    }
}
