<?php

declare(strict_types=1);

namespace Netgen\Bundle\IbexaSiteApiBundle\QueryType;

use Ibexa\Contracts\Core\Repository\Values\ValueObject;

/**
 * QueryDefinition defines a search query through the QueryType configuration.
 *
 * @see \Ibexa\Core\QueryType\QueryType
 *
 * @property string $name QueryType name.
 * @property array $parameters An array of configured QueryType options.
 * @property bool $useFilter Whether to use FilterService or Find Service.
 * @property int $maxPerPage Maximum results per page for Pagerfanta.
 * @property int $page Current page for Pagerfanta.
 *
 * @internal do not depend on this class, it can be changed without warning
 */
final class QueryDefinition extends ValueObject
{
    /**
     * QueryType name.
     *
     * @see \Ibexa\Core\QueryType\QueryType::getName()
     */
    protected string $name;

    /**
     * An array of configured QueryType options.
     */
    protected array $parameters;

    /**
     * Whether to use FilterService or Find Service.
     */
    protected bool $useFilter;

    /**
     * Maximum results per page for Pagerfanta.
     */
    protected int $maxPerPage;

    /**
     * Current page for Pagerfanta.
     */
    protected int $page;
}
