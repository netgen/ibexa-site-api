<?php

declare(strict_types=1);

namespace Netgen\Bundle\IbexaSiteApiBundle\NamedObject;

use Symfony\Component\ExpressionLanguage\ExpressionFunction;
use Symfony\Component\ExpressionLanguage\ExpressionFunctionProviderInterface;

final class ExpressionFunctionProvider implements ExpressionFunctionProviderInterface
{
    public function getFunctions(): array
    {
        return [
            new ExpressionFunction(
                'config',
                static function (): void {},
                static function (array $arguments, string $name, ?string $namespace = null, ?string $scope = null) {
                    /** @var \Ibexa\Contracts\Core\SiteAccess\ConfigResolverInterface $configResolver */
                    $configResolver = $arguments['configResolver'];

                    return $configResolver->getParameter($name, $namespace, $scope);
                },
            ),
        ];
    }
}
