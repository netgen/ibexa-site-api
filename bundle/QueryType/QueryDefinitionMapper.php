<?php

declare(strict_types=1);

namespace Netgen\Bundle\IbexaSiteApiBundle\QueryType;

use Ibexa\Contracts\Core\SiteAccess\ConfigResolverInterface;
use Ibexa\Core\QueryType\QueryTypeRegistry;
use InvalidArgumentException;
use Netgen\Bundle\IbexaSiteApiBundle\View\ContentView;
use Netgen\IbexaSiteApi\Core\Site\QueryType\QueryType as SiteQueryType;

use function array_key_exists;
use function array_replace;
use function is_array;

/**
 * QueryDefinitionMapper maps query configuration to a QueryDefinition instance.
 *
 * @see \Netgen\Bundle\IbexaSiteApiBundle\QueryType\QueryDefinition
 *
 * @internal do not depend on this service, it can be changed without warning
 */
final class QueryDefinitionMapper
{
    private QueryTypeRegistry $queryTypeRegistry;
    private ParameterProcessor $parameterProcessor;
    private ConfigResolverInterface $configResolver;
    private ?array $namedQueryConfiguration = null;

    public function __construct(
        QueryTypeRegistry $queryTypeRegistry,
        ParameterProcessor $parameterProcessor,
        ConfigResolverInterface $configResolver
    ) {
        $this->queryTypeRegistry = $queryTypeRegistry;
        $this->parameterProcessor = $parameterProcessor;
        $this->configResolver = $configResolver;
    }

    /**
     * Map given $configuration in $view context to a QueryDefinition instance.
     *
     * @return \Netgen\Bundle\IbexaSiteApiBundle\QueryType\QueryDefinition
     *
     * @throws \InvalidArgumentException
     */
    public function map(array $configuration, ContentView $view): QueryDefinition
    {
        if (isset($configuration['named_query'])) {
            $namedQueryConfiguration = $this->getQueryConfigurationByName($configuration['named_query']);
            $configuration = $this->overrideConfiguration($namedQueryConfiguration, $configuration);
        }

        return $this->buildQueryDefinition($configuration, $view);
    }

    /**
     * Override $configuration parameters with $override.
     *
     * Only first level keys in main configuration and separately under 'parameters' key are replaced.
     */
    private function overrideConfiguration(array $configuration, array $override): array
    {
        $configuration['parameters'] = array_replace(
            $configuration['parameters'],
            $override['parameters'],
        );

        unset($override['parameters']);

        return array_replace($configuration, $override);
    }

    /**
     * Return named query configuration by the given $name.
     *
     * @throws \InvalidArgumentException if no such configuration exist
     */
    private function getQueryConfigurationByName(string $name): array
    {
        $this->setNamedQueryConfiguration();

        if (array_key_exists($name, $this->namedQueryConfiguration)) {
            return $this->namedQueryConfiguration[$name];
        }

        throw new InvalidArgumentException(
            "Could not find query configuration named '{$name}'",
        );
    }

    private function setNamedQueryConfiguration(): void
    {
        if ($this->namedQueryConfiguration !== null) {
            return;
        }

        $configuration = $this->configResolver->getParameter('ng_site_api.named_queries');

        if ($configuration === null) {
            $configuration = [];
        }

        $this->namedQueryConfiguration = $configuration;
    }

    /**
     * Build QueryDefinition instance from the given arguments.
     *
     * @return \Netgen\Bundle\IbexaSiteApiBundle\QueryType\QueryDefinition
     */
    private function buildQueryDefinition(array $configuration, ContentView $view): QueryDefinition
    {
        $parameters = $this->processParameters($configuration['parameters'], $view);

        $this->injectSupportedParameters($parameters, $configuration['query_type'], $view);

        return new QueryDefinition([
            'name' => $configuration['query_type'],
            'parameters' => $parameters,
            'useFilter' => $this->parameterProcessor->process($configuration['use_filter'], $view),
            'maxPerPage' => $this->parameterProcessor->process($configuration['max_per_page'], $view),
            'page' => $this->parameterProcessor->process($configuration['page'], $view),
        ]);
    }

    /**
     * Inject parameters into $parameters if available in the $view and supported by the QueryType.
     */
    private function injectSupportedParameters(array &$parameters, string $queryTypeName, ContentView $view): void
    {
        $queryType = $this->queryTypeRegistry->getQueryType($queryTypeName);

        if (!$queryType instanceof SiteQueryType) {
            return;
        }

        if (!array_key_exists('content', $parameters) && $queryType->supportsParameter('content')) {
            $parameters['content'] = $view->getSiteContent();
        }

        if (!array_key_exists('location', $parameters) && $queryType->supportsParameter('location')) {
            $parameters['location'] = $view->getSiteLocation();
        }
    }

    /**
     * Recursively process given $parameters using ParameterProcessor.
     *
     * @see \Netgen\Bundle\IbexaSiteApiBundle\QueryType\ParameterProcessor
     */
    private function processParameters(array $parameters, ContentView $view): array
    {
        $processedParameters = [];

        foreach ($parameters as $name => $subParameters) {
            $processedParameters[$name] = $this->recursiveProcessParameters($subParameters, $view);
        }

        return $processedParameters;
    }

    private function recursiveProcessParameters($parameters, ContentView $view)
    {
        if (is_array($parameters)) {
            return $this->processParameters($parameters, $view);
        }

        return $this->parameterProcessor->process($parameters, $view);
    }
}
