<?php

declare(strict_types=1);

namespace Netgen\Bundle\IbexaSiteApiBundle\DependencyInjection\Configuration\Parser\SiteApi;

use Symfony\Component\Config\Definition\Builder\NodeBuilder;
use function array_keys;
use function is_int;
use function is_string;
use function preg_match;

class NamedObjectBuilder
{
    public static function build(NodeBuilder $nodeBuilder): void
    {
        $keyValidator = static function ($v): bool {
            foreach (array_keys($v) as $key) {
                if (!is_string($key) || !preg_match('/[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*$/A', $key)) {
                    return true;
                }
            }

            return false;
        };

        $nodeBuilder
            ->arrayNode('named_objects')
            ->info('Named objects')
            ->children()
                ->arrayNode('content')
                    ->info('Content items by name')
                    ->useAttributeAsKey('name')
                    ->normalizeKeys(false)
                    ->validate()
                        ->ifTrue($keyValidator)
                        ->thenInvalid('Content name must be a string conforming to a valid Twig variable name.')
                    ->end()
                    ->scalarPrototype()
                        ->info('Content ID or remote ID')
                        ->validate()
                            ->ifTrue(static fn ($v) => !is_int($v) && !is_string($v))
                            ->thenInvalid('Content ID or remote ID value must be of integer or string type.')
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('locations')
                    ->info('Locations by name')
                    ->useAttributeAsKey('name')
                    ->normalizeKeys(false)
                    ->validate()
                        ->ifTrue($keyValidator)
                        ->thenInvalid('Location name must be a string conforming to a valid Twig variable name.')
                    ->end()
                    ->scalarPrototype()
                        ->info('Location ID or remote ID')
                        ->validate()
                            ->ifTrue(static fn ($v) => !is_int($v) && !is_string($v))
                            ->thenInvalid('Location ID or remote ID value must be of integer or string type.')
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('tags')
                    ->info('Tags by name')
                    ->useAttributeAsKey('name')
                    ->normalizeKeys(false)
                    ->validate()
                        ->ifTrue($keyValidator)
                        ->thenInvalid('Tags name must be a string conforming to a valid Twig variable name.')
                    ->end()
                    ->scalarPrototype()
                        ->info('Tags ID or remote ID')
                        ->validate()
                            ->ifTrue(static fn ($v) => !is_int($v) && !is_string($v))
                            ->thenInvalid('Tags ID or remote ID value must be of integer or string type.')
                        ->end()
                    ->end()
                ->end()
            ->end()
        ->end();
    }
}
