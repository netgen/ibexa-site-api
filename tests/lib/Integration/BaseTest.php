<?php

declare(strict_types=1);

namespace Netgen\IbexaSiteApi\Tests\Integration;

use Ibexa\Contracts\Core\FieldType\Value;
use Ibexa\Contracts\Core\Repository\Exceptions\PropertyNotFoundException;
use Ibexa\Contracts\Core\Repository\Values\Content\Content as APIContent;
use Ibexa\Contracts\Core\Repository\Values\Content\ContentInfo;
use Ibexa\Contracts\Core\Repository\Values\Content\Field as APIField;
use Ibexa\Contracts\Core\Repository\Values\Content\Location as APILocation;
use Ibexa\Contracts\Core\Repository\Values\Content\VersionInfo;
use Ibexa\Contracts\Core\Repository\Values\ContentType\ContentType;
use Ibexa\Contracts\Core\Repository\Values\User\User;
use Ibexa\Tests\Integration\Core\Repository\BaseTest as APIBaseTest;
use Netgen\IbexaSiteApi\API\Site;
use Netgen\IbexaSiteApi\API\Values\Content;
use Netgen\IbexaSiteApi\API\Values\ContentInfo as APIContentInfo;
use Netgen\IbexaSiteApi\API\Values\Field;
use Netgen\IbexaSiteApi\API\Values\Fields;
use Netgen\IbexaSiteApi\API\Values\Location;
use Netgen\IbexaSiteApi\Core\Site\Values\Field\SurrogateValue;
use ReflectionProperty;

use function array_values;
use function count;
use function get_class;
use function reset;

/**
 * Base class for API integration tests.
 *
 * @internal
 */
abstract class BaseTest extends APIBaseTest
{
    public function getData(string $languageCode): array
    {
        return [
            'name' => $languageCode,
            'contentId' => 61,
            'contentRemoteId' => 'content-remote-id',
            'locationId' => 63,
            'locationRemoteId' => 'location-remote-id',
            'parentLocationId' => 62,
            'contentIsHidden' => false,
            'contentIsVisible' => true,
            'locationPriority' => 1,
            'locationHidden' => false,
            'locationInvisible' => false,
            'locationExplicitlyHidden' => false,
            'locationIsVisible' => true,
            'locationPathString' => '/1/2/62/63/',
            'locationPath' => [1, 2, 62, 63],
            'locationDepth' => 3,
            'locationSortField' => APILocation::SORT_FIELD_NODE_ID,
            'locationSortOrder' => APILocation::SORT_ORDER_DESC,
            'contentTypeIdentifier' => 'test-type',
            'contentTypeId' => 37,
            'sectionId' => 1,
            'currentVersionNo' => 1,
            'published' => true,
            'ownerId' => 14,
            'modificationDateTimestamp' => 100,
            'publishedDateTimestamp' => 101,
            'alwaysAvailable' => true,
            'mainLanguageCode' => 'eng-GB',
            'mainLocationId' => 63,
            'contentTypeName' => 'Test type',
            'contentTypeDescription' => 'A test type',
            'languageCode' => $languageCode,
            'fields' => [
                'title' => [
                    'name' => 'Title',
                    'description' => 'Title of the test type',
                    'fieldTypeIdentifier' => 'ezstring',
                    'value' => $languageCode,
                    'isEmpty' => false,
                    'isSurrogate' => false,
                ],
            ],
            'siblingLocationId' => 64,
            'childLocationId' => 65,
        ];
    }

    protected function overrideSettings(string $name, mixed $value): void
    {
        $settings = $this->getSite()->getSettings();

        $property = new ReflectionProperty(get_class($settings), $name);
        $property->setAccessible(true);
        $property->setValue($settings, $value);
    }

    protected function getSite(): Site
    {
        /** @var \Netgen\IbexaSiteApi\API\Site $site */
        $site = $this->getSetupFactory()->getServiceContainer()->get('netgen.ibexa_site_api.site');

        return $site;
    }

    protected function assertContent(Content $content, array $data): void
    {
        [$name, $contentId, , $locationId] = array_values($data);

        self::assertSame($contentId, $content->id);
        self::assertSame($locationId, $content->mainLocationId);
        self::assertSame($name, $content->name);
        self::assertSame($data['mainLocationId'], $content->mainLocationId);
        self::assertSame($data['languageCode'], $content->languageCode);
        self::assertSame($data['contentIsVisible'], $content->isVisible);
        $this->assertContentInfo($content->contentInfo, $data);
        $this->assertFields($content, $data);
        self::assertInstanceOf(Location::class, $content->mainLocation);
        self::assertInstanceOf(Content::class, $content->owner);
        self::assertInstanceOf(Content::class, $content->owner);
        self::assertInstanceOf(User::class, $content->innerOwnerUser);
        self::assertInstanceOf(User::class, $content->innerOwnerUser);
        self::assertInstanceOf(Content::class, $content->modifier);
        self::assertInstanceOf(Content::class, $content->modifier);
        self::assertInstanceOf(User::class, $content->innerModifierUser);
        self::assertInstanceOf(User::class, $content->innerModifierUser);
        self::assertInstanceOf(APIContent::class, $content->innerContent);
        self::assertInstanceOf(VersionInfo::class, $content->versionInfo);
        self::assertInstanceOf(VersionInfo::class, $content->innerVersionInfo);

        $locations = $content->getLocations();
        self::assertIsArray($locations);
        self::assertCount(1, $locations);
        self::assertInstanceOf(Location::class, reset($locations));

        self::assertTrue(isset($content->id));
        self::assertTrue(isset($content->name));
        self::assertTrue(isset($content->mainLocationId));
        self::assertTrue(isset($content->contentInfo));
        self::assertFalse(isset($content->nonExistentProperty));

        try {
            $content->nonExistentProperty;
            self::fail('This property should not be found');
        } catch (PropertyNotFoundException) {
            // Do nothing
        }
    }

    protected function assertContentInfo(APIContentInfo $contentInfo, array $data): void
    {
        [$name, $contentId, $contentRemoteId, $locationId] = array_values($data);

        self::assertSame($contentId, $contentInfo->id);
        self::assertSame($contentRemoteId, $contentInfo->remoteId);
        self::assertSame($locationId, $contentInfo->mainLocationId);
        self::assertSame($name, $contentInfo->name);
        self::assertSame($data['contentTypeIdentifier'], $contentInfo->contentTypeIdentifier);
        self::assertSame($data['sectionId'], $contentInfo->sectionId);
        self::assertSame($data['currentVersionNo'], $contentInfo->currentVersionNo);
        self::assertSame($data['published'], $contentInfo->published);
        self::assertSame($data['contentIsHidden'], $contentInfo->isHidden);
        self::assertSame($data['contentIsVisible'], $contentInfo->isVisible);
        self::assertSame($data['ownerId'], $contentInfo->ownerId);
        self::assertSame($data['modificationDateTimestamp'], $contentInfo->modificationDate->getTimestamp());
        self::assertSame($data['publishedDateTimestamp'], $contentInfo->publishedDate->getTimestamp());
        self::assertSame($data['alwaysAvailable'], $contentInfo->alwaysAvailable);
        self::assertSame($data['mainLanguageCode'], $contentInfo->mainLanguageCode);
        self::assertSame($data['mainLocationId'], $contentInfo->mainLocationId);
        self::assertSame($data['contentTypeName'], $contentInfo->contentTypeName);
        self::assertSame($data['contentTypeDescription'], $contentInfo->contentTypeDescription);
        self::assertSame($data['languageCode'], $contentInfo->languageCode);
        self::assertInstanceOf(Location::class, $contentInfo->mainLocation);
        self::assertInstanceOf(ContentInfo::class, $contentInfo->innerContentInfo);
        self::assertInstanceOf(ContentType::class, $contentInfo->innerContentType);

        self::assertTrue(isset($contentInfo->name));
        self::assertTrue(isset($contentInfo->contentTypeIdentifier));
        self::assertTrue(isset($contentInfo->contentTypeName));
        self::assertTrue(isset($contentInfo->contentTypeDescription));
        self::assertTrue(isset($contentInfo->mainLocation));
        self::assertFalse(isset($contentInfo->nonExistentProperty));

        try {
            $contentInfo->nonExistentProperty;
            self::fail('This property should not be found');
        } catch (PropertyNotFoundException) {
            // Do nothing
        }
    }

    protected function assertFields(Content $content, array $data): void
    {
        self::assertInstanceOf(Fields::class, $content->fields);
        self::assertCount(count($data['fields']), $content->fields);

        foreach ($content->fields as $identifier => $field) {
            self::assertInstanceOf(Field::class, $field);
            self::assertInstanceOf(Value::class, $field->value);
            self::assertInstanceOf(APIField::class, $field->innerField);

            $fieldById = $content->getFieldById($field->id);
            $fieldByIdentifier = $content->getField($identifier);
            $fieldByFirstNonEmptyField = $content->getFirstNonEmptyField($identifier);

            self::assertSame($field, $fieldById);
            self::assertSame($field, $fieldByIdentifier);
            self::assertSame($field, $fieldByFirstNonEmptyField);

            $fieldValueById = $content->getFieldValueById($field->id);
            $fieldValueByIdentifier = $content->getFieldValue($identifier);

            self::assertSame($field->value, $fieldValueById);
            self::assertSame($fieldValueById, $fieldValueByIdentifier);

            self::assertSame($content, $field->content);

            self::assertSame($data['fields'][$identifier]['value'], (string) $field->value);
        }

        foreach ($data['fields'] as $identifier => $fieldData) {
            $this->assertField($content, $identifier, $data['languageCode'], $fieldData);
        }

        $content->getField('non_existent_field');
        $content->getFieldById('non_existent_field');
        self::assertInstanceOf(SurrogateValue::class, $content->getFieldValue('non_existent_field'));
        self::assertInstanceOf(SurrogateValue::class, $content->getFieldValueById('non_existent_field'));
        self::assertInstanceOf(SurrogateValue::class, $content->getFirstNonEmptyField('non_existent_field')->value);
    }

    protected function assertField(Content $content, string $identifier, string $languageCode, array $data): void
    {
        /** @var \Netgen\IbexaSiteApi\API\Values\Field|\Netgen\IbexaSiteApi\Core\Site\Values\Field $field */
        $field = $content->getField($identifier);

        self::assertSame($field->id, $field->innerField->id);
        self::assertSame($data['isEmpty'], $field->isEmpty());
        self::assertSame($data['isSurrogate'], $field->isSurrogate());
        self::assertSame($identifier, $field->fieldDefIdentifier);
        self::assertSame($data['fieldTypeIdentifier'], $field->fieldTypeIdentifier);
        self::assertSame($data['name'], $field->name);
        self::assertSame($data['description'], $field->description);
        self::assertSame($languageCode, $field->languageCode);

        self::assertTrue(isset($field->fieldTypeIdentifier));
        self::assertTrue(isset($field->innerFieldDefinition));
        self::assertTrue(isset($field->name));
        self::assertTrue(isset($field->description));
        self::assertTrue(isset($field->languageCode));
        self::assertFalse(isset($field->nonExistantProperty));

        try {
            $field->nonExistentProperty;
            self::fail('This property should not be found');
        } catch (PropertyNotFoundException) {
            // Do nothing
        }
    }

    protected function assertLocation(Location $location, array $data): void
    {
        [, , , $locationId, $locationRemoteId, $parentLocationId] = array_values($data);

        self::assertSame($locationId, $location->id);
        self::assertSame($locationRemoteId, $location->remoteId);
        self::assertSame($parentLocationId, $location->parentLocationId);
        self::assertSame(APILocation::STATUS_PUBLISHED, $location->status);
        self::assertSame($data['locationPriority'], $location->priority);
        self::assertSame($data['locationHidden'], $location->hidden);
        self::assertSame($data['locationInvisible'], $location->invisible);
        self::assertSame($data['locationExplicitlyHidden'], $location->explicitlyHidden);
        self::assertSame($data['locationIsVisible'], $location->isVisible);
        self::assertSame($data['locationPathString'], $location->pathString);
        self::assertEquals($data['locationPath'], $location->pathArray);
        self::assertSame($data['locationDepth'], $location->depth);
        self::assertSame($data['locationSortField'], $location->sortField);
        self::assertSame($data['locationSortOrder'], $location->sortOrder);
        self::assertSame($location->contentInfo->id, $location->contentId);
        $this->assertContentInfo($location->contentInfo, $data);
        self::assertInstanceOf(APILocation::class, $location->innerLocation);
        self::assertInstanceOf(Content::class, $location->content);

        self::assertInstanceOf(Location::class, $location->parent);
        self::assertSame($parentLocationId, $location->parent->id);

        $children = $location->getChildren();
        self::assertIsArray($children);
        self::assertCount(1, $children);
        self::assertInstanceOf(Location::class, $children[0]);
        self::assertSame($data['childLocationId'], $children[0]->id);

        $firstChild = $location->getFirstChild();
        self::assertInstanceOf(Location::class, $firstChild);
        self::assertSame($data['childLocationId'], $firstChild->id);

        $siblings = $location->getSiblings();
        self::assertIsArray($siblings);
        self::assertCount(1, $siblings);
        self::assertInstanceOf(Location::class, $siblings[0]);
        self::assertSame($data['siblingLocationId'], $siblings[0]->id);

        self::assertTrue(isset($location->contentId));
        self::assertTrue(isset($location->contentInfo));
        self::assertFalse(isset($location->nonExistentProperty));

        try {
            $location->nonExistentProperty;
            self::fail('This property should not be found');
        } catch (PropertyNotFoundException) {
            // Do nothing
        }
    }
}
