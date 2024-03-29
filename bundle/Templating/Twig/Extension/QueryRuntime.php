<?php

declare(strict_types=1);

namespace Netgen\Bundle\IbexaSiteApiBundle\Templating\Twig\Extension;

use Ibexa\Contracts\Core\Repository\Values\Content\Search\SearchResult;
use Netgen\Bundle\IbexaSiteApiBundle\QueryType\QueryDefinitionCollection;
use Netgen\Bundle\IbexaSiteApiBundle\QueryType\QueryExecutor;
use Netgen\Bundle\IbexaSiteApiBundle\View\ContentView;
use Pagerfanta\Pagerfanta;
use Twig\Error\RuntimeError;

use function sprintf;

/**
 * Twig extension runtime for executing queries from the QueryDefinitionCollection injected
 * into the template.
 */
class QueryRuntime
{
    public function __construct(
        private readonly QueryExecutor $queryExecutor,
    ) {}

    public function executeQuery(mixed $context, string $name): Pagerfanta
    {
        return $this->queryExecutor->execute(
            $this->getQueryDefinitionCollection($context)->get($name),
        );
    }

    public function sudoExecuteQuery(mixed $context, string $name): Pagerfanta
    {
        return $this->queryExecutor->sudoExecute(
            $this->getQueryDefinitionCollection($context)->get($name),
        );
    }

    public function executeRawQuery(mixed $context, string $name): SearchResult
    {
        return $this->queryExecutor->executeRaw(
            $this->getQueryDefinitionCollection($context)->get($name),
        );
    }

    public function sudoExecuteRawQuery(mixed $context, string $name): SearchResult
    {
        return $this->queryExecutor->sudoExecuteRaw(
            $this->getQueryDefinitionCollection($context)->get($name),
        );
    }

    /**
     * Returns the QueryDefinitionCollection variable from the given $context.
     *
     * @throws \Twig\Error\RuntimeError
     */
    private function getQueryDefinitionCollection(mixed $context): QueryDefinitionCollection
    {
        return $context[ContentView::QUERY_DEFINITION_COLLECTION_NAME] ?? throw new RuntimeError(
            sprintf(
                "Could not find QueryDefinitionCollection variable '%s'",
                ContentView::QUERY_DEFINITION_COLLECTION_NAME,
            ),
        );
    }
}
