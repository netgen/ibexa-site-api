<?php

declare(strict_types=1);

namespace Netgen\Bundle\IbexaSiteApiBundle\Routing;

use Ibexa\Contracts\Core\Repository\Repository;
use Ibexa\Contracts\Core\Repository\Values\Content\Location;
use Ibexa\Contracts\Core\SiteAccess\ConfigResolverInterface;
use Ibexa\Core\MVC\Symfony\Routing\Generator\UrlAliasGenerator as BaseUrlAliasGenerator;
use Symfony\Component\Routing\RouterInterface;

class UrlAliasGenerator extends BaseUrlAliasGenerator
{
    private Repository $repository;

    public function __construct(
        Repository $repository,
        RouterInterface $defaultRouter,
        ConfigResolverInterface $configResolver,
        array $unsafeCharMap = []
    ) {
        parent::__construct($repository, $defaultRouter, $configResolver, $unsafeCharMap);

        $this->repository = $repository;
    }

    public function loadLocation($locationId): Location
    {
        return $this->repository->sudo(
            static fn (Repository $repository) => $repository->getLocationService()->loadLocation($locationId, []),
        );
    }
}
