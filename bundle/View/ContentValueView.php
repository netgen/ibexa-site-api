<?php

declare(strict_types=1);

namespace Netgen\Bundle\IbexaSiteApiBundle\View;

use Ibexa\Core\MVC\Symfony\View\ContentValueView as BaseContentValueView;
use Netgen\IbexaSiteApi\API\Values\Content;

interface ContentValueView extends BaseContentValueView
{
    public function getSiteContent(): ?Content;
}
