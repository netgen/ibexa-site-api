<?php

declare(strict_types=1);

namespace Netgen\Bundle\IbexaSiteApiBundle\View\LocationResolver;

use Exception;
use Ibexa\Contracts\Core\Repository\Repository;
use Ibexa\Core\Base\Exceptions\NotFoundException;
use Netgen\Bundle\IbexaSiteApiBundle\View\LocationResolver;
use Netgen\IbexaSiteApi\API\LoadService;
use Netgen\IbexaSiteApi\API\Values\Content;
use Netgen\IbexaSiteApi\API\Values\Location;

class SudoMainLocationResolver extends LocationResolver
{
    public function __construct(
        private readonly Repository $repository,
        private readonly LoadService $loadService,
    ) {
    }

    public function getLocation(Content $content): Location
    {
        if ($content->mainLocationId === null) {
            throw new NotFoundException('Main Location of Content', $content->id);
        }

        try {
            return $this->repository->sudo(
                fn (Repository $repository): Location => $this->loadService->loadLocation($content->mainLocationId),
            );
        } catch (Exception $exception) {
            throw new NotFoundException('Main Location of Content', $content->id, $exception);
        }
    }
}
