<?php

declare(strict_types=1);

namespace Netgen\IbexaSiteApi\Core\Site\Values;

use Netgen\IbexaSiteApi\API\Values\Field as APIField;

final class Field extends APIField
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @var string
     */
    protected $fieldDefIdentifier;

    /**
     * @var \Ibexa\Contracts\Core\FieldType\Value
     */
    protected $value;

    /**
     * @var string
     */
    protected $languageCode;

    /**
     * @var string
     */
    protected $fieldTypeIdentifier;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $description;

    /**
     * @var \Netgen\IbexaSiteApi\API\Values\Content
     */
    protected $content;

    /**
     * @var \Ibexa\Contracts\Core\Repository\Values\Content\Field
     */
    protected $innerField;

    /**
     * @var \Ibexa\Contracts\Core\Repository\Values\ContentType\FieldDefinition
     */
    protected $innerFieldDefinition;

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
