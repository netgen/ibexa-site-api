<?php

declare(strict_types=1);

namespace Netgen\Bundle\IbexaSiteApiBundle\View\Redirect;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\ExpressionLanguage\ExpressionFunction;
use Symfony\Component\ExpressionLanguage\ExpressionFunctionProviderInterface;

class ExpressionFunctionProvider implements ExpressionFunctionProviderInterface
{
    public function __construct(
        private readonly ContainerInterface $container,
    ) {}

    public function getFunctions(): array
    {
        return [
            new ExpressionFunction(
                'namedContent',
                static function (): void {},
                static function (array $arguments, string $name) {
                    /** @var \Netgen\Bundle\IbexaSiteApiBundle\NamedObject\Provider $namedObjectProvider */
                    $namedObjectProvider = $arguments['namedObject'];

                    return $namedObjectProvider->getContent($name);
                },
            ),
            new ExpressionFunction(
                'namedLocation',
                static function (): void {},
                static function (array $arguments, string $name) {
                    /** @var \Netgen\Bundle\IbexaSiteApiBundle\NamedObject\Provider $namedObjectProvider */
                    $namedObjectProvider = $arguments['namedObject'];

                    return $namedObjectProvider->getLocation($name);
                },
            ),
            new ExpressionFunction(
                'namedTag',
                static function (): void {},
                static function (array $arguments, string $name) {
                    /** @var \Netgen\Bundle\IbexaSiteApiBundle\NamedObject\Provider $namedObjectProvider */
                    $namedObjectProvider = $arguments['namedObject'];

                    return $namedObjectProvider->getTag($name);
                },
            ),
            new ExpressionFunction(
                'config',
                static function (): void {},
                static function (array $arguments, string $name, ?string $namespace = null, ?string $scope = null) {
                    /** @var \Ibexa\Contracts\Core\SiteAccess\ConfigResolverInterface $configResolver */
                    $configResolver = $arguments['configResolver'];

                    return $configResolver->getParameter($name, $namespace, $scope);
                },
            ),
            new ExpressionFunction(
                'parameter',
                static function (): void {},
                fn (array $arguments, string $name) => $this->container->getParameter($name),
            ),
        ];
    }
}
