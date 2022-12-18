<?php

declare(strict_types=1);

namespace Netgen\IbexaSiteApi\API\Values;

use Ibexa\Contracts\Core\Repository\Values\ValueObject;

/**
 * Site ContentInfo object provides meta information of the Site Content object.
 *
 * Corresponds to Ibexa Repository ContentInfo object.
 *
 * @see \Ibexa\Contracts\Core\Repository\Values\Content\ContentInfo
 *
 * @property-read int $id
 * @property-read int $contentTypeId
 * @property-read int $sectionId
 * @property-read int $currentVersionNo
 * @property-read bool $published
 * @property-read bool $isHidden
 * @property-read bool $isVisible
 * @property-read int $ownerId
 * @property-read \DateTime $modificationDate
 * @property-read \DateTime $publishedDate
 * @property-read bool $alwaysAvailable
 * @property-read string $remoteId
 * @property-read string $mainLanguageCode
 * @property-read int $mainLocationId
 * @property-read string $name
 * @property-read string $languageCode
 * @property-read string $contentTypeIdentifier
 * @property-read string $contentTypeName
 * @property-read string $contentTypeDescription
 * @property-read \Ibexa\Contracts\Core\Repository\Values\Content\ContentInfo $innerContentInfo
 * @property-read \Ibexa\Contracts\Core\Repository\Values\ContentType\ContentType $innerContentType
 * @property-read ?\Netgen\IbexaSiteApi\API\Values\Location $mainLocation
 */
abstract class ContentInfo extends ValueObject
{
}
