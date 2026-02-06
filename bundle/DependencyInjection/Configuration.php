<?php

declare(strict_types=1);

namespace Netgen\Bundle\IbexaSiteApiBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

readonly class Configuration implements ConfigurationInterface
{
    public function __construct(
        protected string $rootNodeName,
    ) {}

    public function getConfigTreeBuilder(): TreeBuilder
    {
        return new TreeBuilder($this->rootNodeName);
    }
}
