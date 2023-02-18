<?php

declare(strict_types=1);

namespace Netgen\IbexaSiteApi\Core\Site\Values;

use Ibexa\Contracts\Core\FieldType\Value;
use Ibexa\Contracts\Core\Repository\Values\Content\Field as RepositoryField;
use Ibexa\Contracts\Core\Repository\Values\ContentType\FieldDefinition;
use Netgen\IbexaSiteApi\API\Values\Content as APIContent;
use Netgen\IbexaSiteApi\API\Values\Field as APIField;

final class Field extends APIField
{
    protected int $id;
    protected string $fieldDefIdentifier;
    protected Value $value;
    protected string $languageCode;
    protected string $fieldTypeIdentifier;
    protected ?string $name;
    protected ?string $description;
    protected APIContent $content;
    protected RepositoryField $innerField;
    protected FieldDefinition $innerFieldDefinition;
    private bool $isEmpty;
    private bool $isSurrogate;

    public function __construct(array $properties = [])
    {
        $this->isEmpty = $properties['isEmpty'];
        $this->isSurrogate = $properties['isSurrogate'];

        unset($properties['isEmpty'], $properties['isSurrogate']);

        parent::__construct($properties);
    }

    public function __debugInfo(): array
    {
        return [
            'id' => $this->id,
            'fieldDefIdentifier' => $this->fieldDefIdentifier,
            'value' => $this->value,
            'languageCode' => $this->languageCode,
            'fieldTypeIdentifier' => $this->fieldTypeIdentifier,
            'name' => $this->name,
            'description' => $this->description,
            'content' => '[An instance of Netgen\IbexaSiteApi\API\Values\Content]',
            'contentId' => $this->content->id,
            'isEmpty' => $this->isEmpty,
            'isSurrogate' => $this->isSurrogate,
            'innerField' => '[An instance of Ibexa\Contracts\Core\Repository\Values\Content\Field]',
            'innerFieldDefinition' => $this->innerFieldDefinition,
        ];
    }

    public function isEmpty(): bool
    {
        return $this->isEmpty;
    }

    public function isSurrogate(): bool
    {
        return $this->isSurrogate;
    }
}
