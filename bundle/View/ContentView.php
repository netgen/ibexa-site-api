<?php

declare(strict_types=1);

namespace Netgen\Bundle\IbexaSiteApiBundle\View;

use Ibexa\Contracts\Core\Repository\Values\Content\Content as RepoContent;
use Ibexa\Contracts\Core\Repository\Values\Content\Location as RepoLocation;
use Ibexa\Core\MVC\Symfony\View\BaseView;
use Ibexa\Core\MVC\Symfony\View\CachableView;
use Ibexa\Core\MVC\Symfony\View\EmbedView;
use Netgen\IbexaSiteApi\API\Values\Content;
use Netgen\IbexaSiteApi\API\Values\Location;
use RuntimeException;

/**
 * Builds ContentView objects.
 */
class ContentView extends BaseView implements ContentValueView, LocationValueView, EmbedView, CachableView
{
    /**
     * Name of the QueryDefinitionCollection variable injected to the template.
     *
     * @see \Netgen\Bundle\IbexaSiteApiBundle\QueryType\QueryDefinitionCollection
     */
    public const QUERY_DEFINITION_COLLECTION_NAME = 'ng_query_definition_collection';

    private Content $content;
    private ?Location $location = null;
    private ?bool $isEmbed = false;

    public function setSiteContent(Content $content): void
    {
        $this->content = $content;
    }

    public function getContent(): RepoContent
    {
        return $this->content->innerContent;
    }

    public function setSiteLocation(Location $location): void
    {
        $this->location = $location;
    }

    public function getLocation(): ?RepoLocation
    {
        if (!$this->location instanceof Location) {
            return null;
        }

        return $this->location->innerLocation;
    }

    public function getSiteContent(): Content
    {
        return $this->content;
    }

    public function getSiteLocation(): ?Location
    {
        return $this->location;
    }

    public function setContent(): never
    {
        throw new RuntimeException(
            'Method setContent() cannot be used with Site API content view. Use setSiteContent() method instead.',
        );
    }

    public function setLocation(): never
    {
        throw new RuntimeException(
            'Method setLocation() cannot be used with Site API content view. Use setSiteLocation() method instead.',
        );
    }

    /**
     * Sets the value as embed / not embed.
     *
     * @param bool $value
     */
    public function setIsEmbed($value): void
    {
        $this->isEmbed = (bool) $value;
    }

    /**
     * Is the view an embed or not.
     *
     * @return bool true if the view is an embed, false if it is not
     */
    public function isEmbed(): bool
    {
        return $this->isEmbed;
    }

    protected function getInternalParameters(): array
    {
        $parameters = ['content' => $this->content];

        if ($this->location !== null) {
            $parameters['location'] = $this->location;
        }

        return $parameters;
    }
}
