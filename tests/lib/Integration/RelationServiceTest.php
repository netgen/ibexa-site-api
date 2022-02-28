<?php

declare(strict_types=1);

namespace Netgen\IbexaSiteApi\Tests\Integration;

use Ibexa\Contracts\Core\Repository\Values\ContentType\FieldDefinitionCreateStruct;
use Netgen\IbexaSiteApi\API\Values\Content;
use Netgen\IbexaSiteApi\API\Values\Location;

/**
 * Test case for the RelationService.
 *
 * @see \Netgen\IbexaSiteApi\API\RelationService
 *
 * @group integration
 * @group relation
 *
 * @internal
 */
final class RelationServiceTest extends BaseTest
{
    /**
     * @throws \ErrorException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\BadStateException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\ContentFieldValidationException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\ContentTypeFieldDefinitionValidationException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\ContentValidationException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException
     */
    public function testLoadFieldRelation(): void
    {
        [$identifier, $testApiContent, $testRelationId] = $this->prepareTestContent();

        $relationService = $this->getSite()->getRelationService();
        $loadService = $this->getSite()->getLoadService();
        $testSiteContent = $loadService->loadContent($testApiContent->id);
        $content = $relationService->loadFieldRelation($testSiteContent, $identifier);

        self::assertInstanceOf(Content::class, $content);
        self::assertSame($testRelationId, $content->id);
    }

    /**
     * @throws \ErrorException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\BadStateException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\ContentFieldValidationException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\ContentTypeFieldDefinitionValidationException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\ContentValidationException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException
     */
    public function testLoadFieldRelations(): void
    {
        [$identifier, , , $testApiContent, $testRelationIds] = $this->prepareTestContent();

        $relationService = $this->getSite()->getRelationService();
        $loadService = $this->getSite()->getLoadService();
        $testSiteContent = $loadService->loadContent($testApiContent->id);
        $contentItems = $relationService->loadFieldRelations($testSiteContent, $identifier);

        self::assertSameSize($testRelationIds, $contentItems);

        foreach ($testRelationIds as $index => $relationId) {
            $content = $contentItems[$index];
            self::assertInstanceOf(Content::class, $content);
            self::assertSame($relationId, $content->id);
        }
    }

    /**
     * @throws \ErrorException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\BadStateException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\ContentFieldValidationException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\ContentTypeFieldDefinitionValidationException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\ContentValidationException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException
     */
    public function testLoadFieldRelationsWithTypeFilter(): void
    {
        [$identifier, , , $testApiContent, $testRelationIds] = $this->prepareTestContent();

        $relationService = $this->getSite()->getRelationService();
        $loadService = $this->getSite()->getLoadService();
        $testSiteContent = $loadService->loadContent($testApiContent->id);
        $contentItems = $relationService->loadFieldRelations($testSiteContent, $identifier, ['landing_page']);

        self::assertCount(1, $contentItems);

        self::assertInstanceOf(Content::class, $contentItems[0]);
        self::assertSame($testRelationIds[0], $contentItems[0]->id);
    }

    /**
     * @throws \ErrorException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\BadStateException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\ContentFieldValidationException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\ContentTypeFieldDefinitionValidationException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\ContentValidationException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException
     */
    public function testLoadFieldRelationsWithLimit(): void
    {
        [$identifier, , , $testApiContent, $testRelationIds] = $this->prepareTestContent();

        $relationService = $this->getSite()->getRelationService();
        $loadService = $this->getSite()->getLoadService();
        $testSiteContent = $loadService->loadContent($testApiContent->id);
        $contentItems = $relationService->loadFieldRelations($testSiteContent, $identifier, [], 1);

        self::assertCount(1, $contentItems);

        self::assertInstanceOf(Content::class, $contentItems[0]);
        self::assertSame($testRelationIds[0], $contentItems[0]->id);
    }

    /**
     * @throws \ErrorException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\BadStateException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\ContentFieldValidationException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\ContentTypeFieldDefinitionValidationException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\ContentValidationException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException
     */
    public function testLoadFieldRelationsWithTypeFilterAndLimit(): void
    {
        [$identifier, , , $testApiContent, $testRelationIds] = $this->prepareTestContent();

        $relationService = $this->getSite()->getRelationService();
        $loadService = $this->getSite()->getLoadService();
        $testSiteContent = $loadService->loadContent($testApiContent->id);
        $contentItems = $relationService->loadFieldRelations($testSiteContent, $identifier, ['feedback_form'], 1);

        self::assertCount(1, $contentItems);

        self::assertInstanceOf(Content::class, $contentItems[0]);
        self::assertSame($testRelationIds[1], $contentItems[0]->id);
    }

    /**
     * @throws \ErrorException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\BadStateException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\ContentFieldValidationException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\ContentTypeFieldDefinitionValidationException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\ContentValidationException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException
     */
    public function testLoadFieldRelationsForNonexistentField(): void
    {
        [, , , $testApiContent] = $this->prepareTestContent();

        $relationService = $this->getSite()->getRelationService();
        $loadService = $this->getSite()->getLoadService();
        $testSiteContent = $loadService->loadContent($testApiContent->id);
        $contentItems = $relationService->loadFieldRelations($testSiteContent, 'nonexistent');

        self::assertCount(0, $contentItems);
    }

    /**
     * @throws \Exception
     */
    public function testLoadFieldRelationLocation(): void
    {
        [$identifier, $testApiContent, $testRelationId] = $this->prepareTestContent();

        $relationService = $this->getSite()->getRelationService();
        $loadService = $this->getSite()->getLoadService();
        $testSiteContent = $loadService->loadContent($testApiContent->id);
        $location = $relationService->loadFieldRelationLocation($testSiteContent, $identifier);

        self::assertInstanceOf(Location::class, $location);
        self::assertSame($testRelationId, $location->content->id);
    }

    /**
     * @throws \Exception
     */
    public function testLoadFieldRelationLocations(): void
    {
        [$identifier, , , $testApiContent, $testRelationIds] = $this->prepareTestContent();

        $relationService = $this->getSite()->getRelationService();
        $loadService = $this->getSite()->getLoadService();
        $testSiteContent = $loadService->loadContent($testApiContent->id);
        $locations = $relationService->loadFieldRelationLocations($testSiteContent, $identifier);

        self::assertSameSize($testRelationIds, $locations);

        foreach ($testRelationIds as $index => $relationId) {
            $location = $locations[$index];
            self::assertInstanceOf(Location::class, $location);
            self::assertSame($relationId, $location->content->id);
        }
    }

    /**
     * @throws \Exception
     */
    public function testLoadFieldRelationLocationsWithTypeFilter(): void
    {
        [$identifier, , , $testApiContent, $testRelationIds] = $this->prepareTestContent();

        $relationService = $this->getSite()->getRelationService();
        $loadService = $this->getSite()->getLoadService();
        $testSiteContent = $loadService->loadContent($testApiContent->id);
        $locations = $relationService->loadFieldRelationLocations($testSiteContent, $identifier, ['landing_page']);

        self::assertCount(1, $locations);

        self::assertInstanceOf(Location::class, $locations[0]);
        self::assertSame($testRelationIds[0], $locations[0]->content->id);
    }

    /**
     * @throws \Exception
     */
    public function testLoadFieldRelationLocationsWithLimit(): void
    {
        [$identifier, , , $testApiContent, $testRelationIds] = $this->prepareTestContent();

        $relationService = $this->getSite()->getRelationService();
        $loadService = $this->getSite()->getLoadService();
        $testSiteContent = $loadService->loadContent($testApiContent->id);
        $locations = $relationService->loadFieldRelationLocations($testSiteContent, $identifier, [], 1);

        self::assertCount(1, $locations);

        self::assertInstanceOf(Location::class, $locations[0]);
        self::assertSame($testRelationIds[0], $locations[0]->content->id);
    }

    /**
     * @throws \Exception
     */
    public function testLoadFieldRelationLocationsWithTypeFilterAndLimit(): void
    {
        [$identifier, , , $testApiContent, $testRelationIds] = $this->prepareTestContent();

        $relationService = $this->getSite()->getRelationService();
        $loadService = $this->getSite()->getLoadService();
        $testSiteContent = $loadService->loadContent($testApiContent->id);
        $locations = $relationService->loadFieldRelationLocations($testSiteContent, $identifier, ['feedback_form'], 1);

        self::assertCount(1, $locations);

        self::assertInstanceOf(Location::class, $locations[0]);
        self::assertSame($testRelationIds[1], $locations[0]->content->id);
    }

    /**
     * @throws \Exception
     */
    public function testLoadFieldRelationLocationsForNonexistentField(): void
    {
        [, , , $testApiContent] = $this->prepareTestContent();

        $relationService = $this->getSite()->getRelationService();
        $loadService = $this->getSite()->getLoadService();
        $testSiteContent = $loadService->loadContent($testApiContent->id);
        $locations = $relationService->loadFieldRelationLocations($testSiteContent, 'nonexistent');

        self::assertCount(0, $locations);
    }

    /**
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\BadStateException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\ContentFieldValidationException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\ContentTypeFieldDefinitionValidationException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\ContentValidationException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException
     */
    protected function prepareTestContent(): array
    {
        $repository = $this->getRepository();
        $contentTypeService = $repository->getContentTypeService();
        $contentService = $repository->getContentService();
        $languageCode = 'eng-GB';
        $relationId = 57;
        $relationIds = [57, 58];
        $fieldDefinitionIdentifier = 'relation';

        $contentTypeGroup = $contentTypeService->loadContentTypeGroup(1);

        $contentTypeCreateStruct = $contentTypeService->newContentTypeCreateStruct('test_relation');
        $contentTypeCreateStruct->mainLanguageCode = $languageCode;
        $contentTypeCreateStruct->names = [$languageCode => 'Test Relation'];
        $contentTypeCreateStruct->addFieldDefinition(
            new FieldDefinitionCreateStruct([
                'identifier' => $fieldDefinitionIdentifier,
                'fieldTypeIdentifier' => 'ezobjectrelation',
            ]),
        );
        $contentTypeDraft = $contentTypeService->createContentType($contentTypeCreateStruct, [$contentTypeGroup]);
        $contentTypeService->publishContentTypeDraft($contentTypeDraft);

        $contentCreateStruct = $contentService->newContentCreateStruct($contentTypeDraft, $languageCode);
        $contentCreateStruct->setField($fieldDefinitionIdentifier, $relationId);
        $contentDraft = $contentService->createContent($contentCreateStruct);
        $relationContent = $contentService->publishVersion($contentDraft->versionInfo);

        $contentTypeCreateStruct = $contentTypeService->newContentTypeCreateStruct('test_relation_list');
        $contentTypeCreateStruct->mainLanguageCode = $languageCode;
        $contentTypeCreateStruct->names = [$languageCode => 'Test RelationList'];
        $contentTypeCreateStruct->addFieldDefinition(
            new FieldDefinitionCreateStruct([
                'identifier' => $fieldDefinitionIdentifier,
                'fieldTypeIdentifier' => 'ezobjectrelationlist',
            ]),
        );
        $contentTypeDraft = $contentTypeService->createContentType($contentTypeCreateStruct, [$contentTypeGroup]);
        $contentTypeService->publishContentTypeDraft($contentTypeDraft);

        $contentCreateStruct = $contentService->newContentCreateStruct($contentTypeDraft, $languageCode);
        $contentCreateStruct->setField($fieldDefinitionIdentifier, $relationIds);
        $contentDraft = $contentService->createContent($contentCreateStruct);
        $relationListContent = $contentService->publishVersion($contentDraft->versionInfo);

        return [$fieldDefinitionIdentifier, $relationContent, $relationId, $relationListContent, $relationIds];
    }
}
