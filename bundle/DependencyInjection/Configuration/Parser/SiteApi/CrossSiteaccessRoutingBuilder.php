<?php

declare(strict_types=1);

namespace Netgen\Bundle\IbexaSiteApiBundle\DependencyInjection\Configuration\Parser\SiteApi;

use Symfony\Component\Config\Definition\Builder\NodeBuilder;
use Symfony\Component\Config\Definition\Exception\InvalidTypeException;

class CrossSiteaccessRoutingBuilder
{
    public static function build(NodeBuilder $nodeBuilder): void
    {
        $nodeBuilder
            ->arrayNode('cross_siteaccess_routing')
                ->info('Cross-siteaccess routing configuration')
                ->beforeNormalization()
                    // Boolean value is a shortcut to the "enabled" key
                    ->always(static fn ($v) => is_bool($v) ? ['enabled' => $v] : $v)
                ->end()
                ->children()
                    ->booleanNode('enabled')
                        ->info('Controls whether cross-siteaccess router will be used')
                    ->end()
                    ->arrayNode('external_subtree_roots')
                        ->info('A list of allowed subtree root Location IDs external to the subtree root of the current siteaccess')
                        ->beforeNormalization()->always(static fn ($v) => is_int($v) ? [$v] : $v)->end()
                        ->integerPrototype()->end()
                    ->end()
                    ->arrayNode('included_siteaccesses')
                        ->info('A list of included siteaccesses')
                        ->beforeNormalization()->always(static fn ($v) => is_string($v) ? [$v] : $v)->end()
                        ->scalarPrototype()
                            ->beforeNormalization()
                                ->always(static function ($v) {
                                    if (!is_string($v)) {
                                        throw new InvalidTypeException(
                                            'Invalid type for path "ng_site_api.cross_siteaccess_routing.included_siteaccesses". Expected "string", but got "' . gettype($v) . '".'
                                        );
                                    }

                                    return $v;
                                })
                            ->end()
                        ->end()
                    ->end()
                    ->arrayNode('included_siteaccess_groups')
                        ->info('A list of included siteaccess groups')
                        ->beforeNormalization()->always(static fn ($v) => is_string($v) ? [$v] : $v)->end()
                        ->scalarPrototype()
                            ->beforeNormalization()
                                ->always(static function ($v) {
                                    if (!is_string($v)) {
                                        throw new InvalidTypeException(
                                            'Invalid type for path "ng_site_api.cross_siteaccess_routing.included_siteaccess_groups". Expected "string", but got "' . gettype($v) . '".'
                                        );
                                    }

                                    return $v;
                                })
                            ->end()
                        ->end()
                    ->end()
                    ->arrayNode('excluded_siteaccesses')
                        ->info('A list of excluded siteaccesses')
                        ->beforeNormalization()->always(static fn ($v) => is_string($v) ? [$v] : $v)->end()
                        ->scalarPrototype()
                            ->beforeNormalization()
                                ->always(static function ($v) {
                                    if (!is_string($v)) {
                                        throw new InvalidTypeException(
                                            'Invalid type for path "ng_site_api.cross_siteaccess_routing.excluded_siteaccesses". Expected "string", but got "' . gettype($v) . '".'
                                        );
                                    }

                                    return $v;
                                })
                            ->end()
                        ->end()
                    ->end()
                    ->arrayNode('excluded_siteaccess_groups')
                        ->info('A list of excluded siteaccess groups')
                        ->beforeNormalization()->always(static fn ($v) => is_string($v) ? [$v] : $v)->end()
                        ->scalarPrototype()
                            ->beforeNormalization()
                                ->always(static function ($v) {
                                    if (!is_string($v)) {
                                        throw new InvalidTypeException(
                                            'Invalid type for path "ng_site_api.cross_siteaccess_routing.excluded_siteaccess_groups". Expected "string", but got "' . gettype($v) . '".'
                                        );
                                    }

                                    return $v;
                                })
                            ->end()
                        ->end()
                    ->end()
                    ->booleanNode('prefer_main_language')
                        ->info('Controls whether the main language should be preferred')
                    ->end()
                ->end()
            ->end();
    }
}
