<?php

declare(strict_types=1);

namespace Netgen\Bundle\IbexaSiteApiBundle\QueryType;

use Symfony\Component\ExpressionLanguage\ExpressionFunction;
use Symfony\Component\ExpressionLanguage\ExpressionFunctionProviderInterface;
use function in_array;
use function strtotime;

final class ExpressionFunctionProvider implements ExpressionFunctionProviderInterface
{
    public function getFunctions(): array
    {
        return [
            new ExpressionFunction(
                'viewParam',
                static function (): void {},
                static function (array $arguments, string $name, $default) {
                    /** @var \Netgen\Bundle\IbexaSiteApiBundle\View\ContentView $view */
                    $view = $arguments['view'];

                    if ($view->hasParameter($name)) {
                        return $view->getParameter($name);
                    }

                    return $default;
                }
            ),
            new ExpressionFunction(
                'queryParam',
                static function (): void {},
                static function (array $arguments, string $name, $default, ?array $allowed = null) {
                    /** @var \Symfony\Component\HttpFoundation\Request $request */
                    $request = $arguments['request'];

                    if (!$request->query->has($name)) {
                        return $default;
                    }

                    $value = $request->query->get($name);

                    if ($allowed === null || in_array($value, $allowed, true)) {
                        return $value;
                    }

                    return $default;
                }
            ),
            new ExpressionFunction(
                'queryParamString',
                static function (): void {},
                static function (array $arguments, string $name, string $default, ?array $allowed = null): string {
                    /** @var \Symfony\Component\HttpFoundation\Request $request */
                    $request = $arguments['request'];

                    if (!$request->query->has($name)) {
                        return $default;
                    }

                    $value = (string) $request->query->get($name);

                    if ($allowed === null || in_array($value, $allowed, true)) {
                        return $value;
                    }

                    return $default;
                }
            ),
            new ExpressionFunction(
                'queryParamInt',
                static function (): void {},
                static function (array $arguments, string $name, int $default, ?array $allowed = null): int {
                    /** @var \Symfony\Component\HttpFoundation\Request $request */
                    $request = $arguments['request'];

                    if (!$request->query->has($name)) {
                        return $default;
                    }

                    $value = $request->query->getInt($name);

                    if ($allowed === null || in_array($value, $allowed, true)) {
                        return $value;
                    }

                    return $default;
                }
            ),
            new ExpressionFunction(
                'queryParamFloat',
                static function (): void {},
                static function (array $arguments, string $name, float $default, ?array $allowed = null): float {
                    /** @var \Symfony\Component\HttpFoundation\Request $request */
                    $request = $arguments['request'];

                    if (!$request->query->has($name)) {
                        return $default;
                    }

                    $value = (float) $request->query->get($name);

                    if ($allowed === null || in_array($value, $allowed, true)) {
                        return $value;
                    }

                    return $default;
                }
            ),
            new ExpressionFunction(
                'queryParamBool',
                static function (): void {},
                static function (array $arguments, string $name, bool $default, ?array $allowed = null): bool {
                    /** @var \Symfony\Component\HttpFoundation\Request $request */
                    $request = $arguments['request'];

                    if (!$request->query->has($name)) {
                        return $default;
                    }

                    $value = $request->query->getBoolean($name);

                    if ($allowed === null || in_array($value, $allowed, true)) {
                        return $value;
                    }

                    return $default;
                }
            ),
            new ExpressionFunction(
                'timestamp',
                static function (): void {},
                static function (array $arguments, string $timeString) {
                    return strtotime($timeString);
                }
            ),
            new ExpressionFunction(
                'config',
                static function (): void {},
                static function (array $arguments, string $name, ?string $namespace = null, ?string $scope = null) {
                    /** @var \Ibexa\Contracts\Core\SiteAccess\ConfigResolverInterface $configResolver */
                    $configResolver = $arguments['configResolver'];

                    return $configResolver->getParameter($name, $namespace, $scope);
                }
            ),
            new ExpressionFunction(
                'namedContent',
                static function (): void {},
                static function (array $arguments, string $name) {
                    /** @var \Netgen\Bundle\IbexaSiteApiBundle\NamedObject\Provider $namedObjectProvider */
                    $namedObjectProvider = $arguments['namedObject'];

                    return $namedObjectProvider->getContent($name);
                }
            ),
            new ExpressionFunction(
                'namedLocation',
                static function (): void {},
                static function (array $arguments, string $name) {
                    /** @var \Netgen\Bundle\IbexaSiteApiBundle\NamedObject\Provider $namedObjectProvider */
                    $namedObjectProvider = $arguments['namedObject'];

                    return $namedObjectProvider->getLocation($name);
                }
            ),
            new ExpressionFunction(
                'namedTag',
                static function (): void {},
                static function (array $arguments, string $name) {
                    /** @var \Netgen\Bundle\IbexaSiteApiBundle\NamedObject\Provider $namedObjectProvider */
                    $namedObjectProvider = $arguments['namedObject'];

                    return $namedObjectProvider->getTag($name);
                }
            ),
            new ExpressionFunction(
                'split',
                static function (): void {},
                static function (array $arguments, string $name, string $delimiter) {
                    if (empty($name)) {
                        return null;
                    }

                    return \array_map('\trim', \explode($delimiter, $name));
                }
            ),
            new ExpressionFunction(
                'split',
                static function (): void {},
                static function (array $arguments, string $name, string $delimiter = ',') {
                    if (empty($name)) {
                        return null;
                    }

                    return \array_values(\array_filter(\array_map('\trim', \explode($delimiter, $name))));
                }
            ),
            new ExpressionFunction(
                'fieldValue',
                static function (): void {},
                static function (array $arguments, string $fieldIdentifier) {
                    /** @var \Netgen\IbexaSiteApi\API\Values\Content $content */
                    $content = $arguments['content'];

                    return $content->getFieldValue($fieldIdentifier);
                }
            ),
        ];
    }
}
