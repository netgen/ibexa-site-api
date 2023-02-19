<?php

declare(strict_types=1);

namespace Netgen\IbexaSiteApi\Core\Site;

use Ibexa\Contracts\Core\Repository\ContentTypeService;
use Ibexa\Contracts\Core\Repository\FieldTypeService;
use Ibexa\Contracts\Core\Repository\Repository;
use Ibexa\Contracts\Core\Repository\Values\Content\Field as RepoField;
use Ibexa\Contracts\Core\Repository\Values\Content\Location as RepoLocation;
use Ibexa\Contracts\Core\Repository\Values\Content\VersionInfo;
use Netgen\IbexaSiteApi\API\Site as SiteInterface;
use Netgen\IbexaSiteApi\API\Values\Content as SiteContent;
use Netgen\IbexaSiteApi\API\Values\Field as APIField;
use Netgen\IbexaSiteApi\Core\Site\Values\Content;
use Netgen\IbexaSiteApi\Core\Site\Values\ContentInfo;
use Netgen\IbexaSiteApi\Core\Site\Values\Field;
use Netgen\IbexaSiteApi\Core\Site\Values\Location;
use Psr\Log\LoggerInterface;
use RuntimeException;

/**
 * @internal
 *
 * Domain object mapper is an internal service that maps Ibexa Repository objects
 * to the native domain objects
 */
final class DomainObjectMapper
{
    private readonly FieldTypeService $fieldTypeService;
    private readonly ContentTypeService $contentTypeService;

    public function __construct(
        private readonly SiteInterface $site,
        private readonly Repository $repository,
        private readonly bool $failOnMissingField,
        private readonly LoggerInterface $logger,
    ) {
        $this->contentTypeService = $repository->getContentTypeService();
        $this->fieldTypeService = $repository->getFieldTypeService();
    }

    /**
     * Maps Repository Content to the Site Content.
     */
    public function mapContent(VersionInfo $versionInfo, string $languageCode): Content
    {
        $contentInfo = $versionInfo->contentInfo;

        return new Content(
            [
                'id' => $contentInfo->id,
                'mainLocationId' => $contentInfo->mainLocationId,
                'name' => $versionInfo->getName($languageCode),
                'languageCode' => $languageCode,
                'innerVersionInfo' => $versionInfo,
                'site' => $this->site,
                'domainObjectMapper' => $this,
                'repository' => $this->repository,
            ],
            $this->failOnMissingField,
            $this->logger,
        );
    }

    /**
     * Maps Repository ContentInfo to the Site ContentInfo.
     */
    public function mapContentInfo(VersionInfo $versionInfo, string $languageCode): ContentInfo
    {
        $contentInfo = $versionInfo->contentInfo;
        $contentType = $this->contentTypeService->loadContentType($contentInfo->contentTypeId);

        return new ContentInfo(
            [
                'name' => $versionInfo->getName($languageCode),
                'languageCode' => $languageCode,
                'contentTypeIdentifier' => $contentType->identifier,
                'contentTypeName' => $this->getTranslatedString($languageCode, (array) $contentType->getNames()),
                'contentTypeDescription' => $this->getTranslatedString($languageCode, (array) $contentType->getDescriptions()),
                'innerContentInfo' => $versionInfo->contentInfo,
                'innerContentType' => $contentType,
                'site' => $this->site,
            ],
        );
    }

    /**
     * Maps Repository Location to the Site Location.
     */
    public function mapLocation(RepoLocation $location, VersionInfo $versionInfo, string $languageCode): Location
    {
        return new Location(
            [
                'innerLocation' => $location,
                'languageCode' => $languageCode,
                'innerVersionInfo' => $versionInfo,
                'site' => $this->site,
                'domainObjectMapper' => $this,
            ],
            $this->logger,
        );
    }

    /**
     * Maps Repository Field to the Site Field.
     */
    public function mapField(RepoField $apiField, SiteContent $content): APIField
    {
        $fieldDefinition = $content->contentInfo->innerContentType->getFieldDefinition($apiField->fieldDefIdentifier);

        if ($fieldDefinition === null) {
            throw new RuntimeException(
                "Could not find FieldDefinition for '{$apiField->fieldDefIdentifier}' field",
            );
        }

        $fieldTypeIdentifier = $fieldDefinition->fieldTypeIdentifier;
        $isEmpty = $this->fieldTypeService->getFieldType($fieldTypeIdentifier)->isEmptyValue(
            $apiField->value,
        );

        return new Field([
            'id' => $apiField->id,
            'fieldDefIdentifier' => $fieldDefinition->identifier,
            'value' => $apiField->value,
            'languageCode' => $apiField->languageCode,
            'fieldTypeIdentifier' => $fieldTypeIdentifier,
            'name' => $this->getTranslatedString(
                $content->languageCode,
                (array) $fieldDefinition->getNames(),
            ),
            'description' => $this->getTranslatedString(
                $content->languageCode,
                (array) $fieldDefinition->getDescriptions(),
            ),
            'content' => $content,
            'innerField' => $apiField,
            'innerFieldDefinition' => $fieldDefinition,
            'isEmpty' => $isEmpty,
            'isSurrogate' => false,
        ]);
    }

    private function getTranslatedString(string $languageCode, array $strings)
    {
        return $strings[$languageCode] ?? null;
    }
}
