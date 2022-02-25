<?php

declare(strict_types=1);

namespace Netgen\IbexaSiteApi\Tests\Integration;

use DateTimeImmutable;
use Ibexa\Contracts\Core\Repository\Values\Content\Content;
use Ibexa\Contracts\Core\Repository\Values\Content\Location;
use Ibexa\Contracts\Core\Repository\Values\ContentType\ContentType;
use Ibexa\Tests\Integration\Core\Repository\BaseTest as APIBaseTest;

/**
 * Base class for API integration tests.
 *
 * @group load
 * @group find
 * @group filter
 *
 * @internal
 */
final class PrepareFixturesTest extends APIBaseTest
{
    /**
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\BadStateException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\ContentFieldValidationException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\ContentTypeFieldDefinitionValidationException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\ContentValidationException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException
     */
    public function testPrepareTestFixtures(): array
    {
        $contentType = $this->createContentType();
        $container = $this->createContent(
            $contentType,
            2,
            'content-remote-id1',
            'location-remote-id1',
            'eng-GB',
            ['eng-GB'],
            true
        );
        $content = $this->createContent(
            $contentType,
            $container->contentInfo->mainLocationId,
            'content-remote-id',
            'location-remote-id',
            'eng-GB',
            ['eng-GB', 'ger-DE'],
            true
        );
        $this->createContent(
            $contentType,
            $container->contentInfo->mainLocationId,
            'content-remote-id2',
            'location-remote-id2',
            'eng-GB',
            ['eng-GB'],
            true
        );
        $this->createContent(
            $contentType,
            $content->contentInfo->mainLocationId,
            'content-remote-id3',
            'location-remote-id3',
            'eng-GB',
            ['eng-GB'],
            true
        );

        $this->addToAssertionCount(1);

        return [
            'contentId' => $content->id,
            'contentRemoteId' => $content->contentInfo->remoteId,
        ];
    }

    /**
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\BadStateException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\ContentFieldValidationException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\ContentValidationException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException
     */
    protected function createContent(
        ContentType $contentType,
        $parentLocationId,
        string $contentRemoteId,
        string $locationRemoteId,
        string $mainLanguageCode,
        array $languageCodes,
        bool $alwaysAvailable = false
    ): Content {
        $repository = $this->getRepository(false);

        $contentService = $repository->getContentService();
        $locationService = $repository->getLocationService();

        $locationCreateStruct = $locationService->newLocationCreateStruct($parentLocationId);

        $locationCreateStruct->priority = 1;
        $locationCreateStruct->hidden = false;
        $locationCreateStruct->remoteId = $locationRemoteId;
        $locationCreateStruct->sortField = Location::SORT_FIELD_NODE_ID;
        $locationCreateStruct->sortOrder = Location::SORT_ORDER_DESC;

        $contentCreateStruct = $contentService->newContentCreateStruct($contentType, $mainLanguageCode);

        $contentCreateStruct->remoteId = $contentRemoteId;
        $contentCreateStruct->sectionId = 1;
        $contentCreateStruct->alwaysAvailable = $alwaysAvailable;

        foreach ($languageCodes as $languageCode) {
            $contentCreateStruct->setField('title', $languageCode, $languageCode);
        }

        $contentDraft = $contentService->createContent($contentCreateStruct, [$locationCreateStruct]);
        $content = $contentService->publishVersion($contentDraft->getVersionInfo());

        $contentMetadataUpdateStruct = $contentService->newContentMetadataUpdateStruct();
        $contentMetadataUpdateStruct->modificationDate = new DateTimeImmutable('@100');
        $contentMetadataUpdateStruct->publishedDate = new DateTimeImmutable('@101');

        $contentService->updateContentMetadata($content->contentInfo, $contentMetadataUpdateStruct);

        return $contentService->loadContent($content->id);
    }

    /**
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\BadStateException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\ContentTypeFieldDefinitionValidationException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException
     */
    protected function createContentType(): ContentType
    {
        $contentTypeService = $this->getRepository(false)->getContentTypeService();

        $typeCreateStruct = $contentTypeService->newContentTypeCreateStruct('test-type');
        $typeCreateStruct->mainLanguageCode = 'eng-GB';
        $typeCreateStruct->remoteId = 'test-type';
        $typeCreateStruct->urlAliasSchema = '<title>';
        $typeCreateStruct->nameSchema = '<title>';
        $typeCreateStruct->names = [
            'eng-GB' => 'Test type',
            'ger-DE' => 'Test type',
        ];
        $typeCreateStruct->descriptions = [
            'eng-GB' => 'A test type',
            'ger-DE' => 'A test type',
        ];
        $typeCreateStruct->creatorId = 14;
        $typeCreateStruct->creationDate = $this->createDateTime();

        $fieldCreateStruct = $contentTypeService->newFieldDefinitionCreateStruct(
            'title',
            'ezstring'
        );
        $fieldCreateStruct->names = [
            'eng-GB' => 'Title',
            'ger-DE' => 'Title',
        ];
        $fieldCreateStruct->descriptions = [
            'eng-GB' => 'Title of the test type',
            'ger-DE' => 'Title of the test type',
        ];
        $fieldCreateStruct->fieldGroup = 'blog-content';
        $fieldCreateStruct->position = 1;
        $fieldCreateStruct->isTranslatable = true;
        $fieldCreateStruct->isRequired = true;
        $fieldCreateStruct->isInfoCollector = false;
        $fieldCreateStruct->validatorConfiguration = [
            'StringLengthValidator' => [
                'minStringLength' => 0,
                'maxStringLength' => 0,
            ],
        ];
        $fieldCreateStruct->fieldSettings = [];
        $fieldCreateStruct->isSearchable = true;

        $typeCreateStruct->addFieldDefinition($fieldCreateStruct);

        $group = $contentTypeService->loadContentTypeGroupByIdentifier('Content');
        $contentTypeDraft = $contentTypeService->createContentType($typeCreateStruct, [$group]);
        $contentTypeService->publishContentTypeDraft($contentTypeDraft);

        return $contentTypeService->loadContentType($contentTypeDraft->id);
    }
}
