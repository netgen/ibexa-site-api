<?php

declare(strict_types=1);

namespace Netgen\IbexaSiteApi\Core\Site\Values;

use Ibexa\Contracts\Core\Repository\Values\Content\ContentInfo as RepositoryContentInfo;
use Ibexa\Contracts\Core\Repository\Values\ContentType\ContentType;
use Netgen\IbexaSiteApi\API\Site;
use Netgen\IbexaSiteApi\API\Values\ContentInfo as APIContentInfo;
use Netgen\IbexaSiteApi\API\Values\Location as APILocation;

use function property_exists;

final class ContentInfo extends APIContentInfo
{
    protected ?string $name;
    protected string $languageCode;
    protected string $contentTypeIdentifier;
    protected ?string $contentTypeName;
    protected ?string $contentTypeDescription;
    protected RepositoryContentInfo $innerContentInfo;
    protected ContentType $innerContentType;
    private Site $site;
    private ?APILocation $internalMainLocation = null;

    public function __construct(array $properties = [])
    {
        $this->site = $properties['site'];
        unset($properties['site']);

        parent::__construct($properties);
    }

    /**
     * {@inheritdoc}
     *
     * Magic getter for retrieving convenience properties.
     *
     * @param string $property Name of the property to retrieve
     */
    public function __get($property)
    {
        switch ($property) {
            case 'mainLocation':
                return $this->getMainLocation();

            case 'isVisible':
                return !$this->isHidden;
        }

        if (property_exists($this, $property)) {
            return $this->{$property};
        }

        if (property_exists($this->innerContentInfo, $property)) {
            return $this->innerContentInfo->{$property};
        }

        return parent::__get($property);
    }

    /**
     * Magic isset for signaling existence of convenience properties.
     *
     * @param string $property
     */
    public function __isset($property): bool
    {
        switch ($property) {
            case 'mainLocation':
            case 'isVisible':
                return true;
        }

        if (property_exists($this, $property) || property_exists($this->innerContentInfo, $property)) {
            return true;
        }

        return parent::__isset($property);
    }

    private function getMainLocation(): ?APILocation
    {
        if ($this->internalMainLocation === null && $this->mainLocationId !== null) {
            $this->internalMainLocation = $this->site->getLoadService()->loadLocation(
                $this->innerContentInfo->mainLocationId,
            );
        }

        return $this->internalMainLocation;
    }
}
