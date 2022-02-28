<?php

declare(strict_types=1);

namespace Netgen\Bundle\IbexaSiteApiBundle\View\Redirect;

use Netgen\Bundle\IbexaSiteApiBundle\NamedObject\Provider;
use Netgen\Bundle\IbexaSiteApiBundle\View\ContentView;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use function is_string;
use function mb_stripos;
use function mb_substr;

final class ParameterProcessor
{
    private Provider $namedObjectProvider;

    public function __construct(
        Provider $namedObjectProvider
    ) {
        $this->namedObjectProvider = $namedObjectProvider;
    }

    /**
     * Return given $value processed with ExpressionLanguage if needed.
     *
     * Parameter $view is used to provide values for evaluation.
     *
     * @param mixed $value
     */
    public function process($value, ContentView $view)
    {
        if (!is_string($value) || mb_stripos($value, '@=') !== 0) {
            return $value;
        }

        $language = new ExpressionLanguage();

        $this->registerFunctions($language);

        return $language->evaluate(
            mb_substr($value, 2),
            [
                'location' => $view->getSiteLocation(),
                'content' => $view->getSiteContent(),
                'namedObject' => $this->namedObjectProvider,
            ],
        );
    }

    /**
     * Register functions with the given $expressionLanguage.
     */
    private function registerFunctions(ExpressionLanguage $expressionLanguage): void
    {
        $expressionLanguage->register(
            'namedContent',
            static function (): void {},
            fn (array $arguments, string $name) => $this->namedObjectProvider->getContent($name),
        );

        $expressionLanguage->register(
            'namedLocation',
            static function (): void {},
            fn (array $arguments, string $name) => $this->namedObjectProvider->getLocation($name),
        );

        $expressionLanguage->register(
            'namedTag',
            static function (): void {},
            fn (array $arguments, string $name) => $this->namedObjectProvider->getTag($name),
        );
    }
}
