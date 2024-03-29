<?php

declare(strict_types=1);

namespace Netgen\Bundle\IbexaSiteApiBundle\QueryType;

use Ibexa\Contracts\Core\SiteAccess\ConfigResolverInterface;
use Ibexa\Core\QueryType\QueryTypeRegistry;
use Netgen\Bundle\IbexaSiteApiBundle\View\ContentView;
use Netgen\IbexaSiteApi\Core\Site\QueryType\QueryType as SiteQueryType;
use OutOfBoundsException;

use function array_key_exists;
use function array_replace;
use function is_array;
use function sprintf;

/**
 * QueryDefinitionMapper maps query configuration to a QueryDefinition instance.
 *
 * @see \Netgen\Bundle\IbexaSiteApiBundle\QueryType\QueryDefinition
 *
 * @internal do not depend on this service, it can be changed without warning
 */
final class QueryDefinitionMapper
{
    private ?array $namedQueryConfiguration = null;

    public function __construct(
        private readonly QueryTypeRegistry $queryTypeRegistry,
        private readonly ParameterProcessor $parameterProcessor,
        private readonly ConfigResolverInterface $configResolver,
    ) {}

    /**
     * Map given $configuration in $view context to a QueryDefinition instance.
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
     * @throws \OutOfBoundsException if no such configuration exist
     */
    private function getQueryConfigurationByName(string $name): array
    {
        $this->setNamedQueryConfiguration();

        return $this->namedQueryConfiguration[$name] ?? throw new OutOfBoundsException(
            sprintf("Could not find query configuration named '%s'", $name),
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
