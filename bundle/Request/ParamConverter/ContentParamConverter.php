<?php

declare(strict_types=1);

namespace Netgen\Bundle\IbexaSiteApiBundle\Request\ParamConverter;

use Netgen\IbexaSiteApi\API\Values\Content;

final class ContentParamConverter extends SiteParamConverter
{
    protected function getSupportedClass(): string
    {
        return Content::class;
    }

    protected function getPropertyName(): string
    {
        return 'contentId';
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException
     * @throws \Netgen\IbexaSiteApi\API\Exceptions\TranslationNotMatchedException
     */
    protected function loadValueObject(int $id): Content
    {
        return $this->loadService->loadContent($id);
    }
}
