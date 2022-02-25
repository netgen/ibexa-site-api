<?php

declare(strict_types=1);

namespace Netgen\Bundle\IbexaSiteApiBundle\Templating\Twig\NodeVisitor;

use Netgen\Bundle\IbexaSiteApiBundle\Templating\Twig\Node\GetAttrExpressionDecorator;
use Twig\Environment;
use Twig\Node\Expression\GetAttrExpression;
use Twig\Node\Node;
use Twig\NodeVisitor\NodeVisitorInterface;
use function get_class;

class GetAttrExpressionReplacer implements NodeVisitorInterface
{
    public function enterNode(Node $node, Environment $env): Node
    {
        if (get_class($node) !== GetAttrExpression::class) {
            return $node;
        }

        return new GetAttrExpressionDecorator($node);
    }

    public function leaveNode(Node $node, Environment $env): Node
    {
        return $node;
    }

    public function getPriority(): int
    {
        return 0;
    }
}
