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
    private Repository $repository;
    private LoadService $loadService;

    public function __construct(Repository $repository, LoadService $loadService)
    {
        $this->repository = $repository;
        $this->loadService = $loadService;
    }

    public function getLocation(Content $content): Location
    {
        if ($content->mainLocationId === null) {
            throw new NotFoundException('main Location of Content', $content->id);
        }

        try {
            return $this->repository->sudo(
                function (Repository $repository) use ($content): Location {
                    return $this->loadService->loadLocation($content->mainLocationId);
                }
            );
        } catch (Exception $e) {
            throw new NotFoundException('main Location of Content', $content->id);
        }
    }
}
