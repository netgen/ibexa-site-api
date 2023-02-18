<?php

declare(strict_types=1);

namespace Netgen\Bundle\IbexaSiteApiBundle\Request\ParamConverter;

use Netgen\IbexaSiteApi\API\Values\Location;

final class LocationParamConverter extends SiteParamConverter
{
    protected function getSupportedClass(): string
    {
        return Location::class;
    }

    protected function getPropertyName(): string
    {
        return 'locationId';
    }

    protected function loadValueObject(int $id): Location
    {
        return $this->loadService->loadLocation($id);
    }
}
