<?php

declare(strict_types=1);

namespace Netgen\Bundle\IbexaSiteApiBundle\Request\ValueResolver;

use Netgen\IbexaSiteApi\API\LoadService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;

abstract class SiteValueResolver implements ValueResolverInterface
{
    public function __construct(
        protected readonly LoadService $loadService,
    ) {}

    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        if ($argument->getType() !== $this->getSupportedClass()) {
            return [];
        }

        if (!$request->attributes->has($this->getPropertyName())) {
            return [];
        }

        $valueObjectId = $request->attributes->getInt($this->getPropertyName());

        if ($valueObjectId > 0) {
            return [$this->loadValueObject($valueObjectId)];
        }

        if ($argument->isNullable()) {
            return [null];
        }

        if ($argument->hasDefaultValue()) {
            return [$argument->getDefaultValue()];
        }

        return [];
    }

    abstract protected function getSupportedClass(): string;

    abstract protected function getPropertyName(): string;

    abstract protected function loadValueObject(int $id): mixed;
}
