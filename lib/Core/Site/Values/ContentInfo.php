<?php

declare(strict_types=1);

namespace Netgen\IbexaSiteApi\Core\Site\Values;

use Netgen\IbexaSiteApi\API\Values\ContentInfo as APIContentInfo;
use Netgen\IbexaSiteApi\API\Values\Location as APILocation;
use function array_key_exists;
use function property_exists;

final class ContentInfo extends APIContentInfo
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $languageCode;

    /**
     * @var string
     */
    protected $contentTypeIdentifier;

    /**
     * @var string
     */
    protected $contentTypeName;

    /**
     * @var string
     */
    protected $contentTypeDescription;

    /**
     * @var \Ibexa\Contracts\Core\Repository\Values\Content\ContentInfo
     */
    protected $innerContentInfo;

    /**
     * @var \Ibexa\Contracts\Core\Repository\Values\ContentType\ContentType
     */
    protected $innerContentType;

    /**
     * @var \Netgen\IbexaSiteApi\API\Site
     */
    private $site;

    /**
     * @var \Netgen\IbexaSiteApi\API\Values\Location
     */
    private $internalMainLocation;

    public function __construct(array $properties = [])
    {
        if (array_key_exists('site', $properties)) {
            $this->site = $properties['site'];
            unset($properties['site']);
        }

        parent::__construct($properties);
    }

    /**
     * {@inheritdoc}
     *
     * Magic getter for retrieving convenience properties.
     *
     * @param string $property The name of the property to retrieve
     *
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException
     * @throws \Netgen\IbexaSiteApi\API\Exceptions\TranslationNotMatchedException
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

    public function __debugInfo(): array
    {
        return [
            'id' => $this->innerContentInfo->id,
            'contentTypeId' => $this->innerContentInfo->contentTypeId,
            'sectionId' => $this->innerContentInfo->sectionId,
            'currentVersionNo' => $this->innerContentInfo->currentVersionNo,
            'published' => $this->innerContentInfo->published,
            'isHidden' => $this->innerContentInfo->isHidden,
            'isVisible' => !$this->innerContentInfo->isHidden,
            'ownerId' => $this->innerContentInfo->ownerId,
            'modificationDate' => $this->innerContentInfo->modificationDate,
            'publishedDate' => $this->innerContentInfo->publishedDate,
            'alwaysAvailable' => $this->innerContentInfo->alwaysAvailable,
            'remoteId' => $this->innerContentInfo->remoteId,
            'mainLanguageCode' => $this->innerContentInfo->mainLanguageCode,
            'mainLocationId' => $this->innerContentInfo->mainLocationId,
            'name' => $this->name,
            'languageCode' => $this->languageCode,
            'contentTypeIdentifier' => $this->contentTypeIdentifier,
            'contentTypeName' => $this->contentTypeName,
            'contentTypeDescription' => $this->contentTypeDescription,
            'innerContentInfo' => '[An instance of Ibexa\Contracts\Core\Repository\Values\Content\ContentInfo]',
            'innerContentType' => '[An instance of Ibexa\Contracts\Core\Repository\Values\ContentType\ContentType]',
            'mainLocation' => '[An instance of Netgen\IbexaSiteApi\API\Values\Location]',
        ];
    }

    /**
     * @throws \Netgen\IbexaSiteApi\API\Exceptions\TranslationNotMatchedException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException
     */
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
