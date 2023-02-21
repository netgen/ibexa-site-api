<?php

declare(strict_types=1);

namespace Netgen\IbexaSiteApi\Core\Site\Exceptions;

use Exception;
use Ibexa\Core\Base\Exceptions\Httpable;
use Ibexa\Core\Base\Translatable;
use Ibexa\Core\Base\TranslatableBase;
use Netgen\IbexaSiteApi\API\Exceptions\TranslationNotMatchedException as APITranslationNotMatchedException;

use function var_export;

/**
 * This exception is thrown if the Content translation language could not be resolved.
 */
class TranslationNotMatchedException extends APITranslationNotMatchedException implements Httpable, Translatable
{
    use TranslatableBase;

    /**
     * Generates: Could not match translation for Content '{$contentId}' in context '{$context}'.
     */
    public function __construct(int $contentId, mixed $context, ?Exception $previous = null)
    {
        $this->setMessageTemplate(
            "Could not match translation for Content '%contentId%' in context '%context%'",
        );
        $this->setParameters(
            [
                '%contentId%' => $contentId,
                '%context%' => var_export($context, true),
            ],
        );

        parent::__construct($this->getBaseTranslation(), self::NOT_FOUND, $previous);
    }
}
