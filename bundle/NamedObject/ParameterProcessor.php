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

    private ExpressionLanguage $expressionLanguage;
    private ConfigResolverInterface $configResolver;
    private PermissionResolver $permissionResolver;

    public function __construct(
        ExpressionLanguage $expressionLanguage,
        ConfigResolverInterface $configResolver,
        PermissionResolver $permissionResolver
    ) {
        $this->expressionLanguage = $expressionLanguage;
        $this->configResolver = $configResolver;
        $this->permissionResolver = $permissionResolver;
    }

    /**
     * Return given $value processed with ExpressionLanguage if needed.
     *
     * Parameter $view is used to provide values for evaluation.
     *
     * @param mixed $value
     *
     * @return mixed
     */
    public function process($value)
    {
        return $this->evaluate(
            $value,
            $this->expressionLanguage,
            [
                'configResolver' => $this->configResolver,
                'currentUserId' => $this->permissionResolver->getCurrentUserReference()->getUserId(),
            ]
        );
    }
}
