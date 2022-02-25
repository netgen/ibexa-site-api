<?php

declare(strict_types=1);

namespace Netgen\Bundle\IbexaSiteApiBundle\Templating\Twig\Extension;

use Netgen\Bundle\IbexaSiteApiBundle\Templating\Twig\NodeVisitor\GetAttrExpressionReplacer;
use Twig\Extension\AbstractExtension;

class GetAttrExpressionExtension extends AbstractExtension
{
    /**
     * @return \Twig\NodeVisitor\NodeVisitorInterface[]
     */
    public function getNodeVisitors(): array
    {
        return [
            new GetAttrExpressionReplacer(),
        ];
    }
}
