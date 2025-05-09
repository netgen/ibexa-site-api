<?php

declare(strict_types=1);

namespace Netgen\Bundle\IbexaSiteApiBundle\Templating\Twig\Node;

use Netgen\IbexaSiteApi\Core\Site\Values\Fields;
use Traversable;
use Twig\Compiler;
use Twig\Environment;
use Twig\Extension\CoreExtension;
use Twig\Extension\SandboxExtension;
use Twig\Node\Expression\GetAttrExpression;
use Twig\Node\Node;
use Twig\Source;
use Twig\Template;

final class GetAttrExpressionDecorator extends GetAttrExpression
{
    /** @noinspection MagicMethodsValidityInspection */

    /** @noinspection PhpMissingParentConstructorInspection */
    public function __construct(
        private readonly GetAttrExpression $decoratedExpression,
    ) {}

    public function __toString(): string
    {
        return $this->decoratedExpression->__toString();
    }

    public function enableDefinedTest(): void
    {
        $this->decoratedExpression->enableDefinedTest();
    }

    public function isDefinedTestEnabled(): bool
    {
        return $this->decoratedExpression->isDefinedTestEnabled();
    }

    public function compile(Compiler $compiler): void
    {
        $env = $compiler->getEnvironment();
        $arrayAccessSandbox = false;

        // optimize array calls
        if (
            $this->getAttribute('optimizable')
            && !$this->isDefinedTestEnabled()
            && $this->getAttribute('type') === Template::ARRAY_CALL
            && (!$env->isStrictVariables() || $this->getAttribute('ignore_strict_check'))
        ) {
            $var = '$' . $compiler->getVarName();
            $compiler
                ->raw('((' . $var . ' = ')
                ->subcompile($this->getNode('node'))
                ->raw(') && is_array(')
                ->raw($var);

            if (!$env->hasExtension(SandboxExtension::class)) {
                $compiler
                    ->raw(') || ')
                    ->raw($var)
                    ->raw(' instanceof ArrayAccess ? (')
                    ->raw($var)
                    ->raw('[')
                    ->subcompile($this->getNode('attribute'))
                    ->raw('] ?? null) : null)');

                return;
            }

            $arrayAccessSandbox = true;

            $compiler
                ->raw(') || ')
                ->raw($var)
                ->raw(' instanceof ArrayAccess && in_array(')
                ->raw($var.'::class')
                ->raw(', CoreExtension::ARRAY_LIKE_CLASSES, true) ? (')
                ->raw($var)
                ->raw('[')
                ->subcompile($this->getNode('attribute'))
                ->raw('] ?? null) : ');
        }

        $compiler->raw(self::class . '::twigGetAttribute($this->env, $this->source, ');

        if ($this->getAttribute('ignore_strict_check')) {
            $this->getNode('node')->setAttribute('ignore_strict_check', true);
        }

        $compiler
            ->subcompile($this->getNode('node'))
            ->raw(', ')
            ->subcompile($this->getNode('attribute'));

        if ($this->hasNode('arguments')) {
            $compiler->raw(', ')->subcompile($this->getNode('arguments'));
        } else {
            $compiler->raw(', []');
        }

        $compiler->raw(', ')
            ->repr($this->getAttribute('type'))
            ->raw(', ')->repr($this->isDefinedTestEnabled())
            ->raw(', ')->repr($this->getAttribute('ignore_strict_check'))
            ->raw(', ')->repr($env->hasExtension(SandboxExtension::class))
            ->raw(', ')->repr($this->getNode('node')->getTemplateLine())
            ->raw(')');

        if ($arrayAccessSandbox) {
            $compiler->raw(')');
        }
    }

    public function getTemplateLine(): int
    {
        return $this->decoratedExpression->getTemplateLine();
    }

    public function getNodeTag(): ?string
    {
        return $this->decoratedExpression->getNodeTag();
    }

    public function hasAttribute(string $name): bool
    {
        return $this->decoratedExpression->hasAttribute($name);
    }

    public function getAttribute($name, $default = null)
    {
        return $this->decoratedExpression->getAttribute($name, $default);
    }

    public function setAttribute(string $name, $value): void
    {
        $this->decoratedExpression->setAttribute($name, $value);
    }

    public function removeAttribute(string $name): void
    {
        $this->decoratedExpression->removeAttribute($name);
    }

    public function hasNode(string $name): bool
    {
        return $this->decoratedExpression->hasNode($name);
    }

    public function getNode(string $name): Node
    {
        return $this->decoratedExpression->getNode($name);
    }

    public function setNode(string $name, Node $node): void
    {
        $this->decoratedExpression->setNode($name, $node);
    }

    public function removeNode(string $name): void
    {
        $this->decoratedExpression->removeNode($name);
    }

    public function count()
    {
        return $this->decoratedExpression->count();
    }

    public function getIterator(): Traversable
    {
        return $this->decoratedExpression->getIterator();
    }

    public function getTemplateName(): ?string
    {
        return $this->decoratedExpression->getTemplateName();
    }

    public function setSourceContext(Source $source): void
    {
        $this->decoratedExpression->setSourceContext($source);
    }

    public function getSourceContext(): ?Source
    {
        return $this->decoratedExpression->getSourceContext();
    }

    /**
     * @param string $type
     * @param bool $isDefinedTest
     * @param bool $ignoreStrictCheck
     * @param bool $sandboxed
     * @param int $lineno
     */
    public static function twigGetAttribute(
        Environment $env,
        Source $source,
        mixed $object,
        mixed $item,
        array $arguments = [],
        $type = Template::ANY_CALL,
        $isDefinedTest = false,
        $ignoreStrictCheck = false,
        $sandboxed = false,
        $lineno = -1,
    ) {
        if (!$object instanceof Fields) {
            return CoreExtension::getAttribute(
                $env,
                $source,
                $object,
                $item,
                $arguments,
                $type,
                $isDefinedTest,
                $ignoreStrictCheck,
                $sandboxed,
                $lineno,
            );
        }

        if ($isDefinedTest) {
            return $object->hasField($item);
        }

        return $object->getField($item);
    }
}
