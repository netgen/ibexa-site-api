<?php

declare(strict_types=1);

namespace Netgen\IbexaSiteApi\Core\Site\Values;

use ArrayIterator;
use Ibexa\Contracts\Core\Repository\Values\Content\Field as RepoField;
use Ibexa\Core\Repository\Values\ContentType\FieldDefinition as CoreFieldDefinition;
use Netgen\IbexaSiteApi\API\Values\Content as SiteContent;
use Netgen\IbexaSiteApi\API\Values\Field as APIField;
use Netgen\IbexaSiteApi\API\Values\Fields as APIFields;
use Netgen\IbexaSiteApi\Core\Site\DomainObjectMapper;
use Netgen\IbexaSiteApi\Core\Site\Values\Field\SurrogateValue;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Traversable;

use function array_key_exists;
use function array_merge;
use function count;
use function sprintf;

/**
 * @internal do not depend on this implementation, use API Fields instead
 *
 * @see \Netgen\IbexaSiteApi\API\Values\Fields
 */
final class Fields extends APIFields
{
    private bool $failOnMissingField;
    private bool $areFieldsInitialized = false;

    private ?ArrayIterator $iterator = null;

    private SiteContent $content;
    private DomainObjectMapper $domainObjectMapper;
    private LoggerInterface $logger;

    /** @var \Netgen\IbexaSiteApi\API\Values\Field[] */
    private array $fieldsByIdentifier = [];

    /** @var \Netgen\IbexaSiteApi\API\Values\Field[] */
    private array $fieldsById = [];

    /** @var \Netgen\IbexaSiteApi\API\Values\Field[] */
    private array $fieldsByNumericSequence = [];

    public function __construct(
        SiteContent $content,
        DomainObjectMapper $domainObjectMapper,
        bool $failOnMissingField,
        LoggerInterface $logger,
    ) {
        $this->content = $content;
        $this->domainObjectMapper = $domainObjectMapper;
        $this->failOnMissingField = $failOnMissingField;
        $this->logger = $logger;
    }

    public function __debugInfo(): array
    {
        $this->initialize();

        return $this->fieldsByIdentifier;
    }

    public function getIterator(): Traversable
    {
        $this->initialize();

        return $this->iterator;
    }

    public function offsetExists($offset): bool
    {
        $this->initialize();

        return array_key_exists($offset, $this->fieldsByIdentifier)
            || array_key_exists($offset, $this->fieldsByNumericSequence);
    }

    public function hasField(string $identifier): bool
    {
        $this->initialize();

        return array_key_exists($identifier, $this->fieldsByIdentifier);
    }

    public function getField(string $identifier): APIField
    {
        if ($this->hasField($identifier)) {
            return $this->fieldsByIdentifier[$identifier];
        }

        $message = sprintf('Field "%s" in Content #%s does not exist', $identifier, $this->content->id);

        if ($this->failOnMissingField) {
            throw new RuntimeException($message);
        }

        $this->logger->critical($message . ', using surrogate field instead');

        return $this->getSurrogateField($identifier, $this->content);
    }

    public function offsetGet($offset): APIField
    {
        $this->initialize();

        if (array_key_exists($offset, $this->fieldsByIdentifier)) {
            return $this->fieldsByIdentifier[$offset];
        }

        if (array_key_exists($offset, $this->fieldsByNumericSequence)) {
            return $this->fieldsByNumericSequence[$offset];
        }

        $message = sprintf('Field "%s" in Content #%s does not exist', $offset, $this->content->id);

        if ($this->failOnMissingField) {
            throw new RuntimeException($message);
        }

        $this->logger->critical($message . ', using surrogate field instead');

        return $this->getSurrogateField($offset, $this->content);
    }

    public function offsetSet($offset, $value): void
    {
        throw new RuntimeException('Setting the field to the collection is not allowed');
    }

    public function offsetUnset($offset): void
    {
        throw new RuntimeException('Unsetting the field from the collection is not allowed');
    }

    public function count(): int
    {
        $this->initialize();

        return count($this->fieldsByIdentifier);
    }

    public function hasFieldById($id): bool
    {
        $this->initialize();

        return array_key_exists($id, $this->fieldsById);
    }

    public function getFieldById($id): APIField
    {
        if ($this->hasFieldById($id)) {
            return $this->fieldsById[$id];
        }

        $message = sprintf('Field #%s in Content #%s does not exist', $id, $this->content->id);

        if ($this->failOnMissingField) {
            throw new RuntimeException($message);
        }

        $this->logger->critical($message . ', using surrogate field instead');

        return $this->getSurrogateField((string) $id, $this->content);
    }

    public function getFirstNonEmptyField(string $firstIdentifier, string ...$otherIdentifiers): APIField
    {
        $identifiers = array_merge([$firstIdentifier], $otherIdentifiers);
        $fields = $this->getAvailableFields($identifiers);

        foreach ($fields as $field) {
            if (!$field->isEmpty()) {
                return $field;
            }
        }

        return $fields[0] ?? $this->getSurrogateField($firstIdentifier, $this->content);
    }

    /**
     * @param string[] $identifiers
     *
     * @return \Netgen\IbexaSiteApi\API\Values\Field[]
     */
    private function getAvailableFields(array $identifiers): array
    {
        $fields = [];

        foreach ($identifiers as $identifier) {
            if ($this->hasField($identifier)) {
                $fields[] = $this->getField($identifier);
            }
        }

        return $fields;
    }

    private function initialize(): void
    {
        if ($this->areFieldsInitialized) {
            return;
        }

        $content = $this->content;

        foreach ($content->innerContent->getFieldsByLanguage($content->languageCode) as $apiField) {
            $field = $this->domainObjectMapper->mapField($apiField, $content);

            $this->fieldsByIdentifier[$field->fieldDefIdentifier] = $field;
            $this->fieldsById[$field->id] = $field;
            $this->fieldsByNumericSequence[] = $field;
        }

        $this->iterator = new ArrayIterator($this->fieldsByIdentifier);

        $this->areFieldsInitialized = true;
    }

    private function getSurrogateField(string $identifier, SiteContent $content): Field
    {
        $apiField = new RepoField([
            'id' => 0,
            'fieldDefIdentifier' => $identifier,
            'value' => new SurrogateValue(),
            'languageCode' => $content->languageCode,
            'fieldTypeIdentifier' => 'ngsurrogate',
        ]);

        $fieldDefinition = new CoreFieldDefinition([
            'id' => 0,
            'identifier' => $apiField->fieldDefIdentifier,
            'fieldGroup' => '',
            'position' => 0,
            'fieldTypeIdentifier' => $apiField->fieldTypeIdentifier,
            'isTranslatable' => false,
            'isRequired' => false,
            'isInfoCollector' => false,
            'defaultValue' => null,
            'isSearchable' => false,
            'mainLanguageCode' => $apiField->languageCode,
            'fieldSettings' => [],
            'validatorConfiguration' => [],
        ]);

        return new Field([
            'id' => $apiField->id,
            'fieldDefIdentifier' => $fieldDefinition->identifier,
            'value' => $apiField->value,
            'languageCode' => $apiField->languageCode,
            'fieldTypeIdentifier' => $apiField->fieldTypeIdentifier,
            'name' => '',
            'description' => '',
            'content' => $content,
            'innerField' => $apiField,
            'innerFieldDefinition' => $fieldDefinition,
            'isEmpty' => true,
            'isSurrogate' => true,
        ]);
    }
}
