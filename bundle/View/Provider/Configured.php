<?php

declare(strict_types=1);

namespace Netgen\Bundle\IbexaSiteApiBundle\View\Provider;

use Ibexa\Core\MVC\Symfony\Matcher\MatcherFactoryInterface;
use Ibexa\Core\MVC\Symfony\View\ContentView as CoreContentView;
use Ibexa\Core\MVC\Symfony\View\View;
use Ibexa\Core\MVC\Symfony\View\ViewProvider;
use Netgen\Bundle\IbexaSiteApiBundle\DependencyInjection\Configuration\Parser\ContentView as ContentViewParser;
use Netgen\Bundle\IbexaSiteApiBundle\QueryType\QueryDefinitionCollection;
use Netgen\Bundle\IbexaSiteApiBundle\QueryType\QueryDefinitionMapper;
use Netgen\Bundle\IbexaSiteApiBundle\View\ContentView;
use Netgen\Bundle\IbexaSiteApiBundle\View\Redirect\ParameterProcessor;
use Netgen\Bundle\IbexaSiteApiBundle\View\Redirect\RedirectConfiguration;
use Netgen\Bundle\IbexaSiteApiBundle\View\Redirect\Resolver;
use Symfony\Bundle\FrameworkBundle\Controller\RedirectController;
use Symfony\Component\HttpKernel\Controller\ControllerReference;

use function array_key_exists;
use function is_array;
use function preg_replace;
use function sprintf;

/**
 * Kind of a plugin to the Configurator, uses view configuration.
 *
 * @see \Ibexa\Core\MVC\Symfony\View\Configurator\ViewProvider
 */
class Configured implements ViewProvider
{
    private MatcherFactoryInterface $matcherFactory;
    private QueryDefinitionMapper $queryDefinitionMapper;
    private Resolver $redirectResolver;
    private ContentViewFallbackResolver $contentViewFallbackResolver;
    private ParameterProcessor $parameterProcessor;

    public function __construct(
        MatcherFactoryInterface $matcherFactory,
        QueryDefinitionMapper $queryDefinitionMapper,
        Resolver $redirectResolver,
        ContentViewFallbackResolver $contentViewFallbackResolver,
        ParameterProcessor $parameterProcessor,
    ) {
        $this->matcherFactory = $matcherFactory;
        $this->queryDefinitionMapper = $queryDefinitionMapper;
        $this->redirectResolver = $redirectResolver;
        $this->contentViewFallbackResolver = $contentViewFallbackResolver;
        $this->parameterProcessor = $parameterProcessor;
    }

    /**
     * Returns view as a data transfer object.
     */
    public function getView(View $view): ?View
    {
        // Service is dispatched by the configured view class, so this should be safe
        /** @var \Netgen\Bundle\IbexaSiteApiBundle\View\ContentView $view */
        $configHash = $this->matcherFactory->match($view);

        if ($configHash === null) {
            return $this->contentViewFallbackResolver->getIbexaPlatformFallbackDto($view);
        }

        // We can set the collection directly to the view, no need to go through DTO
        $view->addParameters([
            ContentView::QUERY_DEFINITION_COLLECTION_NAME => $this->getQueryDefinitionCollection($configHash, $view),
        ]);

        // Return DTO so that Configurator can set the data back to the $view
        return $this->getDTO($configHash, $view);
    }

    private function getQueryDefinitionCollection(array $configHash, ContentView $view): QueryDefinitionCollection
    {
        $queryDefinitionCollection = new QueryDefinitionCollection();
        $queriesConfiguration = $this->getQueriesConfiguration($configHash);

        foreach ($queriesConfiguration as $variableName => $queryConfiguration) {
            $queryDefinitionCollection->add(
                $variableName,
                $this->queryDefinitionMapper->map($queryConfiguration, $view),
            );
        }

        return $queryDefinitionCollection;
    }

    private function getQueriesConfiguration(array $configHash): array
    {
        if (array_key_exists(ContentViewParser::QUERY_KEY, $configHash)) {
            return $configHash[ContentViewParser::QUERY_KEY];
        }

        return [];
    }

    /**
     * Builds a ContentView object from $viewConfig.
     */
    private function getDTO(array $viewConfig, ContentView $view): CoreContentView
    {
        $dto = new CoreContentView();
        $dto->setConfigHash($viewConfig);

        $this->processRedirects($dto, $viewConfig, $view);

        if (isset($viewConfig['template'])) {
            $dto->setTemplateIdentifier($this->replaceTemplateIdentifierVariables($viewConfig['template'], $view));
        }

        if (isset($viewConfig['controller'])) {
            $dto->setControllerReference(new ControllerReference($viewConfig['controller']));
        }

        if (isset($viewConfig['params']) && is_array($viewConfig['params'])) {
            $dto->addParameters($viewConfig['params']);
        }

        return $dto;
    }

    private function replaceTemplateIdentifierVariables(string $identifier, ContentView $view): string
    {
        $contentTypeIdentifier = $view->getSiteContent()->contentInfo->contentTypeIdentifier;

        return preg_replace('/{content_type}/', $contentTypeIdentifier, $identifier) ?? $identifier;
    }

    private function processRedirects(CoreContentView $dto, array $viewConfig, ContentView $view): void
    {
        if (!isset($viewConfig['redirect'])) {
            return;
        }

        $dto->setControllerReference(
            new ControllerReference(
                sprintf('%s::%s', RedirectController::class, 'urlRedirectAction'),
            ),
        );

        $config = $this->parameterProcessor->process($viewConfig['redirect'], $view);
        $redirectConfig = RedirectConfiguration::fromConfigurationArray($config);

        $dto->addParameters([
            'path' => $this->redirectResolver->resolveTarget($redirectConfig),
            'permanent' => $redirectConfig->isPermanent(),
            'keepRequestMethod' => $redirectConfig->keepRequestMethod(),
        ]);
    }
}
