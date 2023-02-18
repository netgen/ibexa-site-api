<?php

declare(strict_types=1);

namespace Netgen\Bundle\IbexaSiteApiBundle\Traits;

use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

use function is_array;
use function is_string;
use function mb_strlen;
use function mb_substr;
use function str_starts_with;

/**
 * Common implementation for processing language expressions.
 */
trait LanguageExpressionEvaluatorTrait
{
    private static string $expressionMarker = '@=';

    /**
     * Return given $value processed with ExpressionLanguage if needed.
     *
     * Parameter $view is used to provide values for evaluation.
     */
    protected function evaluate(
        mixed $value,
        ExpressionLanguage $expressionLanguage,
        array $values
    ) {
        if (is_array($value)) {
            return $this->evaluateParameters($value, $expressionLanguage, $values);
        }

        if (!$this->isExpression($value)) {
            return $value;
        }

        return $expressionLanguage->evaluate(
            $this->extractExpression($value),
            $values,
        );
    }

    /**
     * Recursively process given $parameters using ParameterProcessor.
     *
     * @see \Netgen\Bundle\IbexaSiteApiBundle\QueryType\ParameterProcessor
     */
    private function evaluateParameters(
        array $parameters,
        ExpressionLanguage $expressionLanguage,
        array $values
    ): array {
        $processedParameters = [];

        foreach ($parameters as $name => $subParameters) {
            $processedParameters[$name] = $this->evaluate(
                $subParameters,
                $expressionLanguage,
                $values,
            );
        }

        return $processedParameters;
    }

    private function isExpression($value): bool
    {
        return is_string($value) && str_starts_with($value, self::$expressionMarker);
    }

    private function extractExpression(string $value): string
    {
        return mb_substr($value, mb_strlen(self::$expressionMarker));
    }
}
