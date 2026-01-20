<?php

declare(strict_types=1);

namespace Netgen\IbexaSiteApi\Tests\Unit\Core\Site\Values;

use Ibexa\Contracts\Core\Repository\Values\Content\Field as RepoField;
use Ibexa\Core\FieldType\Integer\Value;
use Ibexa\Core\Repository\Values\ContentType\FieldDefinition;
use Ibexa\Core\Repository\Values\ContentType\FieldDefinitionCollection;
use Netgen\IbexaSiteApi\API\Values\Content as APIContent;
use Netgen\IbexaSiteApi\API\Values\Field as SiteField;
use Netgen\IbexaSiteApi\API\Values\Fields as APIFields;
use Netgen\IbexaSiteApi\Core\Site\Values\Content;
use Netgen\IbexaSiteApi\Core\Site\Values\Fields;
use Netgen\IbexaSiteApi\Tests\Unit\Core\Site\ContentFieldsMockTrait;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use RuntimeException;

use function sprintf;

/**
 * Fields value unit tests.
 *
 * @see \Netgen\IbexaSiteApi\API\Values\Fields
 *
 * @internal
 */
#[Group('fields')]
#[AllowMockObjectsWithoutExpectations]
final class FieldsTest extends TestCase
{
    use ContentFieldsMockTrait;

    private ?MockObject $loggerMock = null;

    public function testFieldsCanBeCounted(): void
    {
        $fields = $this->getFieldsUnderTest(true);

        self::assertCount(3, $fields);
    }

    /**
     * @depends testFieldsCanBeCounted
     */
    public function testFieldsCanBeIterated(): void
    {
        $fields = $this->getFieldsUnderTest(true);
        $i = 1;

        self::assertInstanceOf(APIFields::class, $fields);

        foreach ($fields as $field) {
            self::assertSame($i, $field->id);
            ++$i;
        }
    }

    public function testExistenceOfExistingFieldCanBeCheckedByIdentifier(): void
    {
        $fields = $this->getFieldsUnderTest(true);

        self::assertTrue($fields->hasField('first'));
    }

    public function testExistenceOfNonExistingFieldCanBeCheckedByIdentifier(): void
    {
        $fields = $this->getFieldsUnderTest(true);

        self::assertFalse($fields->hasField('fourth'));
    }

    public function testExistenceOfExistingFieldCanBeCheckedAsAnArrayByIdentifier(): void
    {
        $fields = $this->getFieldsUnderTest(true);

        self::assertTrue(isset($fields['first']));
    }

    public function testExistenceOfNonExistingFieldCanBeCheckedAsAnArrayByIdentifier(): void
    {
        $fields = $this->getFieldsUnderTest(true);

        self::assertFalse(isset($fields['fourth']));
    }

    public function testExistenceOfExistingFieldCanBeCheckedAsAnArrayByNumericIndex(): void
    {
        $fields = $this->getFieldsUnderTest(true);

        self::assertTrue(isset($fields[0]));
    }

    public function testExistenceOfNonExistingFieldCanBeCheckedAsAnArrayByNumericIndex(): void
    {
        $fields = $this->getFieldsUnderTest(true);

        self::assertFalse(isset($fields[101]));
    }

    public function testFieldsCanBeAccessedAsAnArrayByNumericIndex(): void
    {
        $fields = $this->getFieldsUnderTest(true);

        for ($i = 0; $i < 3; ++$i) {
            $field = $fields[$i];
            self::assertInstanceOf(SiteField::class, $field);
            self::assertSame($i + 1, $field->id);
        }
    }

    public function testFieldsCanBeAccessedAsAnArrayByIdentifier(): void
    {
        $fields = $this->getFieldsUnderTest(true);
        $identifiers = ['first', 'second', 'third'];

        foreach ($identifiers as $identifier) {
            $field = $fields[$identifier];
            self::assertInstanceOf(SiteField::class, $field);
            self::assertSame($identifier, $field->fieldDefIdentifier);
        }
    }

    public function testAccessingNonExistentFieldThrowsRuntimeException(): void
    {
        $this->expectException(RuntimeException::class);

        $fields = $this->getFieldsUnderTest(true);

        $fields['fourth'];
    }

    public function testAccessingNonExistentFieldReturnsNullField(): void
    {
        $fields = $this->getFieldsUnderTest(false);
        $identifier = 'fourth';

        $loggerMock = $this->getLoggerMock();
        $loggerMock
            ->expects(self::once())
            ->method('critical')
            ->with('Field "fourth" in Content #1 does not exist, using surrogate field instead');

        $field = $fields[$identifier];

        self::assertInstanceOf(SiteField::class, $field);
        self::assertSame($identifier, $field->fieldDefIdentifier);
        self::assertSame('ngsurrogate', $field->fieldTypeIdentifier);
        self::assertTrue($field->isEmpty());
    }

    public function testFieldCanNotBeSet(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Setting the field to the collection is not allowed');

        $fields = $this->getFieldsUnderTest(true);

        $fields['pekmez'] = 'džem';
    }

    public function testFieldCanNotBeUnset(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Unsetting the field from the collection is not allowed');

        $fields = $this->getFieldsUnderTest(true);

        unset($fields['first']);
    }

    public function testExistenceOfExistingFieldCanBeCheckedById(): void
    {
        $fields = $this->getFieldsUnderTest(true);

        self::assertTrue($fields->hasFieldById(1));
    }

    public function testExistenceOfNonExistingFieldCanBeCheckedById(): void
    {
        $fields = $this->getFieldsUnderTest(true);

        self::assertFalse($fields->hasFieldById(101));
    }

    public function testExistingFieldCanBeAccessedById(): void
    {
        $fields = $this->getFieldsUnderTest(true);
        $id = 1;

        $field = $fields->getFieldById($id);

        self::assertSame($id, $field->id);
    }

    public function testNonExistentFieldCanNotBeAccessedById(): void
    {
        $id = 101;

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(sprintf('Field #%s in Content #1 does not exist', $id));

        $fields = $this->getFieldsUnderTest(true);

        $fields->getFieldById($id);
    }

    public function testAccessingNonExistentFieldByIdReturnsNullField(): void
    {
        $id = 101;

        $fields = $this->getFieldsUnderTest(false);

        $field = $fields->getFieldById($id);

        self::assertSame((string) $id, $field->fieldDefIdentifier);
        self::assertSame('ngsurrogate', $field->fieldTypeIdentifier);
        self::assertTrue($field->isEmpty());
    }

    public function testExistingFieldCanBeAccessedByIdentifier(): void
    {
        $fields = $this->getFieldsUnderTest(true);
        $identifier = 'first';

        $field = $fields->getField($identifier);

        self::assertSame($identifier, $field->fieldDefIdentifier);
    }

    public function testNonExistentFieldCanNotBeAccessedByIdentifier(): void
    {
        $identifier = 'fourth';

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(sprintf('Field "%s" in Content #1 does not exist', $identifier));

        $fields = $this->getFieldsUnderTest(true);

        $fields->getField($identifier);
    }

    public function testAccessingNonExistentFieldByIdentifierReturnsNullField(): void
    {
        $identifier = 'fourth';

        $fields = $this->getFieldsUnderTest(false);

        $field = $fields->getField($identifier);

        self::assertSame($identifier, $field->fieldDefIdentifier);
        self::assertSame('ngsurrogate', $field->fieldTypeIdentifier);
        self::assertTrue($field->isEmpty());
    }

    public function testFirstNonEmptyFieldReturnsFirstField(): void
    {
        $identifier = 'first';

        $fields = $this->getFieldsUnderTest(false);

        $field = $fields->getFirstNonEmptyField($identifier, 'second', 'third', 'fourth');

        self::assertSame($identifier, $field->fieldDefIdentifier);
    }

    public function testFirstNonEmptyFieldReturnsFirstNonEmptyField(): void
    {
        $fields = $this->getFieldsUnderTest(false);

        $field = $fields->getFirstNonEmptyField('1st', 'second', 'third', 'fourth');

        self::assertSame('third', $field->fieldDefIdentifier);
    }

    public function testFirstNonEmptyFieldReturnsThirdField(): void
    {
        $identifier = 'third';

        $fields = $this->getFieldsUnderTest(false);

        $field = $fields->getFirstNonEmptyField('1st', '2nd', $identifier, 'fourth');

        self::assertSame($identifier, $field->fieldDefIdentifier);
    }

    public function testFirstNonEmptyFieldReturnsSurrogateField(): void
    {
        $identifier = '1st';

        $fields = $this->getFieldsUnderTest(false);

        $field = $fields->getFirstNonEmptyField($identifier, '2nd', '3rd', '4th');

        self::assertSame($identifier, $field->fieldDefIdentifier);
        self::assertSame('ngsurrogate', $field->fieldTypeIdentifier);
        self::assertTrue($field->isEmpty());
    }

    protected function getFieldsUnderTest(bool $failOnMissingField): Fields
    {
        return new Fields(
            $this->getMockedContent(),
            $this->getDomainObjectMapper(),
            $failOnMissingField,
            $this->getLoggerMock(),
        );
    }

    protected function getLoggerMock(): LoggerInterface|MockObject
    {
        if ($this->loggerMock !== null) {
            return $this->loggerMock;
        }

        $this->loggerMock = $this->getMockBuilder(LoggerInterface::class)->getMock();

        return $this->loggerMock;
    }

    protected function getMockedContent(): APIContent
    {
        return new Content(
            [
                'id' => 1,
                'site' => $this->getSiteMock(),
                'name' => 'Krešo',
                'mainLocationId' => 123,
                'domainObjectMapper' => $this->getDomainObjectMapper(),
                'repository' => $this->getRepositoryMock(),
                'innerVersionInfo' => $this->getRepoVersionInfo(),
                'languageCode' => 'eng-GB',
            ],
            true,
            new NullLogger(),
        );
    }

    protected function internalGetRepoFieldDefinitions(): FieldDefinitionCollection
    {
        return new FieldDefinitionCollection([
            new FieldDefinition([
                'id' => 1,
                'identifier' => 'first',
                'fieldTypeIdentifier' => 'ezinteger',
            ]),
            new FieldDefinition([
                'id' => 2,
                'identifier' => 'second',
                'fieldTypeIdentifier' => 'ezinteger',
            ]),
            new FieldDefinition([
                'id' => 3,
                'identifier' => 'third',
                'fieldTypeIdentifier' => 'ezinteger',
            ]),
        ]);
    }

    protected function internalGetRepoFields(): array
    {
        return [
            new RepoField([
                'id' => 1,
                'fieldDefIdentifier' => 'first',
                'value' => new Value(1),
                'languageCode' => 'eng-GB',
                'fieldTypeIdentifier' => 'ezinteger',
            ]),
            new RepoField([
                'id' => 2,
                'fieldDefIdentifier' => 'second',
                'value' => new Value(),
                'languageCode' => 'eng-GB',
                'fieldTypeIdentifier' => 'ezinteger',
            ]),
            new RepoField([
                'id' => 3,
                'fieldDefIdentifier' => 'third',
                'value' => new Value(3),
                'languageCode' => 'eng-GB',
                'fieldTypeIdentifier' => 'ezinteger',
            ]),
        ];
    }
}
