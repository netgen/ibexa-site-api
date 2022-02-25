<?php

declare(strict_types=1);

namespace Netgen\Bundle\IbexaSiteApiBundle\Templating\Twig\Extension;

use Ibexa\Contracts\Core\Repository\Values\Content\Search\SearchResult;
use Netgen\Bundle\IbexaSiteApiBundle\QueryType\QueryDefinitionCollection;
use Netgen\Bundle\IbexaSiteApiBundle\QueryType\QueryExecutor;
use Netgen\Bundle\IbexaSiteApiBundle\View\ContentView;
use Pagerfanta\Pagerfanta;
use Twig\Error\RuntimeError;
use function array_key_exists;
use function is_array;

/**
 * Twig extension runtime for executing queries from the QueryDefinitionCollection injected
 * into the template.
 */
class QueryRuntime
{
    private QueryExecutor $queryExecutor;

    public function __construct(QueryExecutor $queryExecutor)
    {
        $this->queryExecutor = $queryExecutor;
    }

    /**
     * @throws \Pagerfanta\Exception\Exception
     * @throws \Twig\Error\RuntimeError
     */
    public function executeQuery($context, string $name): Pagerfanta
    {
        return $this->queryExecutor->execute(
            $this->getQueryDefinitionCollection($context)->get($name)
        );
    }

    /**
     * @throws \Twig\Error\RuntimeError
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException
     */
    public function executeRawQuery($context, string $name): SearchResult
    {
        return $this->queryExecutor->executeRaw(
            $this->getQueryDefinitionCollection($context)->get($name)
        );
    }

    /**
     * Returns the QueryDefinitionCollection variable from the given $context.
     *
     * @throws \Twig\Error\RuntimeError
     */
    private function getQueryDefinitionCollection($context): QueryDefinitionCollection
    {
        $variableName = ContentView::QUERY_DEFINITION_COLLECTION_NAME;

        if (is_array($context) && array_key_exists($variableName, $context)) {
            return $context[$variableName];
        }

        throw new RuntimeError(
            "Could not find QueryDefinitionCollection variable '{$variableName}'"
        );
    }
}
