<?php

declare(strict_types=1);

namespace Netgen\Bundle\IbexaSiteApiBundle\View;

use Ibexa\Core\MVC\Symfony\View\LocationValueView as BaseLocationValueView;
use Netgen\IbexaSiteApi\API\Values\Location;

interface LocationValueView extends BaseLocationValueView
{
    public function getSiteLocation(): ?Location;
}
