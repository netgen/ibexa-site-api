<?php

declare(strict_types=1);

namespace Netgen\Bundle\IbexaSiteApiBundle\Templating\Twig\Node;

use Netgen\IbexaSiteApi\Core\Site\Values\Fields;
use Traversable;
use Twig\Compiler;
use Twig\Environment;
use Twig\Extension\SandboxExtension;
use Twig\Node\Expression\GetAttrExpression;
use Twig\Node\Node;
use Twig\Source;
use Twig\Template;

use function twig_get_attribute;

final class GetAttrExpressionDecorator extends GetAttrExpression
{
    private GetAttrExpression $decoratedExpression;

    /** @noinspection MagicMethodsValidityInspection */

    /** @noinspection PhpMissingParentConstructorInspection */
    public function __construct(GetAttrExpression $decoratedExpression)
    {
        $this->decoratedExpression = $decoratedExpression;
    }

    public function __toString(): string
    {
        return $this->decoratedExpression->__toString();
    }

    public function compile(Compiler $compiler): void
    {
        $env = $compiler->getEnvironment();

        // optimize array calls
        if (
            $this->getAttribute('optimizable')
            && !$this->getAttribute('is_defined_test')
            && $this->getAttribute('type') === Template::ARRAY_CALL
            && (!$env->isStrictVariables() || $this->getAttribute('ignore_strict_check'))
        ) {
            $var = '$' . $compiler->getVarName();
            $compiler
                ->raw('((' . $var . ' = ')
                ->subcompile($this->getNode('node'))
                ->raw(') && is_array(')
                ->raw($var)
                ->raw(') || ')
                ->raw($var)
                ->raw(' instanceof ArrayAccess ? (')
                ->raw($var)
                ->raw('[')
                ->subcompile($this->getNode('attribute'))
                ->raw('] ?? null) : null)');

            return;
        }

        $compiler->raw(self::class . '::twig_get_attribute($this->env, $this->source, ');

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
            ->raw(', ')->repr($this->getAttribute('is_defined_test'))
            ->raw(', ')->repr($this->getAttribute('ignore_strict_check'))
            ->raw(', ')->repr($env->hasExtension(SandboxExtension::class))
            ->raw(', ')->repr($this->getNode('node')->getTemplateLine())
            ->raw(')');
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

    public function getAttribute(string $name)
    {
        return $this->decoratedExpression->getAttribute($name);
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

    /**
     * {@inheritdoc}
     *
     * @deprecated
     */
    public function setTemplateName($name): void
    {
        $this->decoratedExpression->setTemplateName($name);
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
     * @param mixed $object
     * @param mixed $item
     *
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException
     * @throws \Twig\Error\RuntimeError
     */
    public static function twig_get_attribute(
        Environment $env,
        Source $source,
        $object,
        $item,
        array $arguments = [],
        $type = 'any',
        $isDefinedTest = false,
        $ignoreStrictCheck = false,
        $sandboxed = false,
        $lineno = -1
    ) {
        if (!$object instanceof Fields) {
            return twig_get_attribute(
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
