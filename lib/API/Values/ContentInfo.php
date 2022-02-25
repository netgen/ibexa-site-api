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
 * @property int $id
 * @property int $contentTypeId
 * @property int $sectionId
 * @property int $currentVersionNo
 * @property bool $published
 * @property bool $isHidden
 * @property bool $isVisible
 * @property int $ownerId
 * @property \DateTime $modificationDate
 * @property \DateTime $publishedDate
 * @property bool $alwaysAvailable
 * @property string $remoteId
 * @property string $mainLanguageCode
 * @property int $mainLocationId
 * @property string $name
 * @property string $languageCode
 * @property string $contentTypeIdentifier
 * @property string $contentTypeName
 * @property string $contentTypeDescription
 * @property \Ibexa\Contracts\Core\Repository\Values\Content\ContentInfo $innerContentInfo
 * @property \Ibexa\Contracts\Core\Repository\Values\ContentType\ContentType $innerContentType
 * @property \Netgen\IbexaSiteApi\API\Values\Location|null $mainLocation
 */
abstract class ContentInfo extends ValueObject
{
}
