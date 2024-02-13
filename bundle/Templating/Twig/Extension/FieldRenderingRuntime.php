<?php

declare(strict_types=1);

namespace Netgen\Bundle\IbexaSiteApiBundle\Templating\Twig\Extension;

use Ibexa\Core\MVC\Symfony\FieldType\View\ParameterProviderRegistryInterface;
use Ibexa\Core\MVC\Symfony\Templating\Twig\FieldBlockRenderer;
use Netgen\IbexaSiteApi\API\Values\Field;
use Twig\Environment;

use function sprintf;

/**
 * Twig extension runtime for content fields rendering (view).
 */
class FieldRenderingRuntime
{
    public function __construct(
        private readonly Environment $environment,
        private readonly FieldBlockRenderer $fieldBlockRenderer,
        private readonly ParameterProviderRegistryInterface $parameterProviderRegistry,
    ) {}

    /**
     * Renders the HTML for a given field.
     *
     * @param array $params An array of parameters to pass to the field view
     *
     * @return string The HTML markup
     */
    public function renderField(Field $field, array $params = []): string
    {
        $this->fieldBlockRenderer->setTwig($this->environment);

        $params = $this->getRenderFieldBlockParameters($field, $params);

        return $this->fieldBlockRenderer->renderContentFieldView(
            $field->innerField,
            $field->fieldTypeIdentifier,
            $params,
        );
    }

    /**
     * Generates the array of parameter to pass to the field template.
     *
     * @param \Netgen\IbexaSiteApi\API\Values\Field $field the Field to display
     * @param array $params An array of parameters to pass to the field view
     */
    private function getRenderFieldBlockParameters(Field $field, array $params = []): array
    {
        // Merging passed parameters to default ones
        $params += [
            'parameters' => [], // parameters dedicated to template processing
            'attr' => [], // attributes to add on the enclosing HTML tags
        ];

        $content = $field->content->innerContent;
        $fieldDefinition = $field->innerFieldDefinition;

        $params += [
            'field' => $field->innerField,
            'content' => $content,
            'contentInfo' => $content->getVersionInfo()->getContentInfo(),
            'versionInfo' => $content->getVersionInfo(),
            'fieldSettings' => $fieldDefinition->getFieldSettings(),
            'contentTypeIdentifier' => $field->content->contentInfo->contentTypeIdentifier,
        ];

        // Adding field type specific parameters if any.
        if ($this->parameterProviderRegistry->hasParameterProvider($fieldDefinition->fieldTypeIdentifier)) {
            $params['parameters'] += $this->parameterProviderRegistry
                ->getParameterProvider($fieldDefinition->fieldTypeIdentifier)
                ->getViewParameters($field->innerField);
        }

        // make sure we can easily add class="<fieldtypeidentifier>-field" to the
        // generated HTML
        $class = sprintf('%s-field', $field->fieldTypeIdentifier);

        if (isset($params['attr']['class'])) {
            $params['attr']['class'] .= ' ' . $class;
        } else {
            $params['attr']['class'] = $class;
        }

        return $params;
    }
}
