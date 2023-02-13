<?php

declare(strict_types=1);

namespace Netgen\Bundle\IbexaSiteApiBundle\DependencyInjection\Configuration\Parser;

use Ibexa\Bundle\Core\DependencyInjection\Configuration\AbstractParser;
use Ibexa\Bundle\Core\DependencyInjection\Configuration\SiteAccessAware\ContextualizerInterface;
use Netgen\Bundle\IbexaSiteApiBundle\DependencyInjection\Configuration\Parser\SiteApi\CrossSiteaccessContentBuilder;
use Netgen\Bundle\IbexaSiteApiBundle\DependencyInjection\Configuration\Parser\SiteApi\NamedObjectBuilder;
use Netgen\Bundle\IbexaSiteApiBundle\DependencyInjection\Configuration\Parser\SiteApi\NamedQueryBuilder;
use Symfony\Component\Config\Definition\Builder\NodeBuilder;

class SiteApi extends AbstractParser
{
    private const NODE_KEY = 'ng_site_api';

    public function addSemanticConfig(NodeBuilder $nodeBuilder): void
    {
        $childrenBuilder = $nodeBuilder->arrayNode('ng_site_api')->info('Site API configuration')->children();

        $childrenBuilder
            ->booleanNode('site_api_is_primary_content_view')
                ->info('Controls whether Site API content view should be used as the primary content view')
            ->end()
            ->booleanNode('fallback_to_secondary_content_view')
                ->info('Controls fallback content view rendering between primary and secondary content view (Site API or Ibexa CMS)')
            ->end()
            ->booleanNode('fallback_without_subrequest')
                ->info('Controls whether secondary content view fallback should use a subrequest')
            ->end()
            ->booleanNode('richtext_embed_without_subrequest')
                ->info('Controls whether RichText embed rendering should use a subrequest')
            ->end()
            ->booleanNode('use_always_available_fallback')
                ->info('Controls missing translation fallback to main language marked as always available')
            ->end()
            ->booleanNode('show_hidden_items')
                ->info('Controls whether hidden Locations and Content items will be shown by default')
            ->end()
            ->booleanNode('fail_on_missing_field')
                ->info('Controls failing on a missing Content Field')
            ->end()
            ->booleanNode('render_missing_field_info')
                ->info('Controls rendering useful debug information in place of a missing field')
            ->end()
            ->booleanNode('enable_internal_view_route')
                ->info('Controls whether internal Content view route will work (applies for a frontend siteaccess only)')
            ->end()
            ->booleanNode('redirect_internal_view_route_to_url_alias')
                ->info('Controls whether internal Content view route will redirect to URL alias route')
            ->end()
        ->end();

        CrossSiteaccessContentBuilder::build($childrenBuilder);
        NamedObjectBuilder::build($childrenBuilder);
        NamedQueryBuilder::build($childrenBuilder);
    }

    public function mapConfig(array &$scopeSettings, $currentScope, ContextualizerInterface $contextualizer): void
    {
        $booleanKeys = [
            'site_api_is_primary_content_view',
            'fallback_to_secondary_content_view',
            'fallback_without_subrequest',
            'richtext_embed_without_subrequest',
            'use_always_available_fallback',
            'show_hidden_items',
            'fail_on_missing_field',
            'render_missing_field_info',
            'enable_internal_view_route',
            'redirect_internal_view_route_to_url_alias',
        ];

        foreach ($booleanKeys as $parameterName) {
            if (isset($scopeSettings[self::NODE_KEY][$parameterName])) {
                $contextualizer->setContextualParameter(
                    self::NODE_KEY . '.' . $parameterName,
                    $currentScope,
                    $scopeSettings[self::NODE_KEY][$parameterName],
                );
            }

            unset($scopeSettings[self::NODE_KEY][$parameterName]);
        }

        if (isset($scopeSettings[self::NODE_KEY]['cross_siteaccess_content']['enabled'])) {
            $contextualizer->setContextualParameter(
                self::NODE_KEY . '.cross_siteaccess_content.enabled',
                $currentScope,
                $scopeSettings[self::NODE_KEY]['cross_siteaccess_content']['enabled'],
            );
        }

        if (isset($scopeSettings[self::NODE_KEY]['cross_siteaccess_content']['external_subtree_roots'])) {
            $contextualizer->setContextualParameter(
                self::NODE_KEY . '.cross_siteaccess_content.external_subtree_roots',
                $currentScope,
                $scopeSettings[self::NODE_KEY]['cross_siteaccess_content']['external_subtree_roots'],
            );
        }

        if (isset($scopeSettings[self::NODE_KEY]['cross_siteaccess_content']['included_siteaccesses'])) {
            $contextualizer->setContextualParameter(
                self::NODE_KEY . '.cross_siteaccess_content.included_siteaccesses',
                $currentScope,
                $scopeSettings[self::NODE_KEY]['cross_siteaccess_content']['included_siteaccesses'],
            );
        }

        if (isset($scopeSettings[self::NODE_KEY]['cross_siteaccess_content']['included_siteaccess_groups'])) {
            $contextualizer->setContextualParameter(
                self::NODE_KEY . '.cross_siteaccess_content.included_siteaccess_groups',
                $currentScope,
                $scopeSettings[self::NODE_KEY]['cross_siteaccess_content']['included_siteaccess_groups'],
            );
        }

        if (isset($scopeSettings[self::NODE_KEY]['cross_siteaccess_content']['excluded_siteaccesses'])) {
            $contextualizer->setContextualParameter(
                self::NODE_KEY . '.cross_siteaccess_content.excluded_siteaccesses',
                $currentScope,
                $scopeSettings[self::NODE_KEY]['cross_siteaccess_content']['excluded_siteaccesses'],
            );
        }

        if (isset($scopeSettings[self::NODE_KEY]['cross_siteaccess_content']['excluded_siteaccess_groups'])) {
            $contextualizer->setContextualParameter(
                self::NODE_KEY . '.cross_siteaccess_content.excluded_siteaccess_groups',
                $currentScope,
                $scopeSettings[self::NODE_KEY]['cross_siteaccess_content']['excluded_siteaccess_groups'],
            );
        }

        if (isset($scopeSettings[self::NODE_KEY]['cross_siteaccess_content']['prefer_main_language'])) {
            $contextualizer->setContextualParameter(
                self::NODE_KEY . '.cross_siteaccess_content.prefer_main_language',
                $currentScope,
                $scopeSettings[self::NODE_KEY]['cross_siteaccess_content']['prefer_main_language'],
            );
        }

        unset($scopeSettings[self::NODE_KEY]['cross_siteaccess_content']);

        if (isset($scopeSettings[self::NODE_KEY]['named_objects'])) {
            $scopeSettings[self::NODE_KEY . '.named_objects'] = $scopeSettings[self::NODE_KEY]['named_objects'];
            unset($scopeSettings[self::NODE_KEY]['named_objects']);
        }

        if (isset($scopeSettings[self::NODE_KEY]['named_queries'])) {
            $scopeSettings[self::NODE_KEY . '.named_queries'] = $scopeSettings[self::NODE_KEY]['named_queries'];
            unset($scopeSettings[self::NODE_KEY]['named_queries']);
        }
    }

    public function postMap(array $config, ContextualizerInterface $contextualizer): void
    {
        $contextualizer->mapConfigArray(self::NODE_KEY . '.named_objects', $config, ContextualizerInterface::MERGE_FROM_SECOND_LEVEL);
        $contextualizer->mapConfigArray(self::NODE_KEY . '.named_queries', $config, ContextualizerInterface::MERGE_FROM_SECOND_LEVEL);
    }
}
