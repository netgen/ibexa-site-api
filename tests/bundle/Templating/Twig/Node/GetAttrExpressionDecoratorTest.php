<?php

declare(strict_types=1);

namespace Netgen\Bundle\IbexaSiteApiBundle\Tests\Templating\Twig\Node;

use Netgen\Bundle\IbexaSiteApiBundle\Templating\Twig\Node\GetAttrExpressionDecorator;
use PHPUnit\Framework\TestCase;
use Twig\Environment;
use Twig\Loader\ArrayLoader;
use Twig\Node\Expression\ConstantExpression;
use Twig\Node\Expression\GetAttrExpression;
use Twig\Node\Node;
use Twig\NodeTraverser;
use Twig\NodeVisitor\NodeVisitorInterface;
use Twig\Template;

use function count;
use function iterator_to_array;
use function method_exists;

/**
 * @internal
 */
final class GetAttrExpressionDecoratorTest extends TestCase
{
    public function testGetNodesReturnsTheNodesOfTheDecoratedExpression(): void
    {
        if (!method_exists(Node::class, 'getNodes')) {
            self::markTestSkipped('Node::getNodes() exists since Twig 3.31.');
        }

        $expression = $this->createExpression();

        self::assertSame($expression->getNodes(), (new GetAttrExpressionDecorator($expression))->getNodes());
    }

    public function testNodeTraverserVisitsTheNodesOfTheDecoratedExpression(): void
    {
        $expression = $this->createExpression();
        $visitor = new class () implements NodeVisitorInterface {
            /** @var array<int, class-string<Node>> */
            public array $visited = [];

            public function enterNode(Node $node, Environment $env): Node
            {
                $this->visited[] = $node::class;

                return $node;
            }

            public function leaveNode(Node $node, Environment $env): ?Node
            {
                return $node;
            }

            public function getPriority(): int
            {
                return 0;
            }
        };

        $traverser = new NodeTraverser(new Environment(new ArrayLoader()), [$visitor]);
        $traverser->traverse(new GetAttrExpressionDecorator($expression));

        self::assertSame(GetAttrExpressionDecorator::class, $visitor->visited[0]);
        self::assertCount(1 + count(iterator_to_array($expression->getIterator())), $visitor->visited);
    }

    private function createExpression(): GetAttrExpression
    {
        return new GetAttrExpression(
            new ConstantExpression('object', 1),
            new ConstantExpression('attribute', 1),
            null,
            Template::ANY_CALL,
            1,
        );
    }
}
