<?php

declare(strict_types=1);

namespace Netgen\Bundle\IbexaSiteApiBundle\NamedObject;

use Ibexa\Contracts\Core\Repository\PermissionResolver;
use Ibexa\Contracts\Core\SiteAccess\ConfigResolverInterface;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use function is_string;
use function mb_strlen;
use function mb_strpos;
use function mb_substr;

/**
 * ParameterProcessor processes query configuration parameter values using ExpressionLanguage.
 *
 * @internal do not depend on this service, it can be changed without warning
 */
final class ParameterProcessor
{
    /**
     * @var string
     */
    private const ExpressionMarker = '@=';

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
        if (!$this->isExpression($value)) {
            return $value;
        }

        return $this->expressionLanguage->evaluate(
            $this->extractExpression($value),
            [
                'configResolver' => $this->configResolver,
                'currentUserId' => $this->permissionResolver->getCurrentUserReference()->getUserId(),
            ],
        );
    }

    private function isExpression($value): bool
    {
        return is_string($value) && mb_strpos($value, self::ExpressionMarker) === 0;
    }

    private function extractExpression(string $value): string
    {
        return mb_substr($value, mb_strlen(self::ExpressionMarker));
    }
}
