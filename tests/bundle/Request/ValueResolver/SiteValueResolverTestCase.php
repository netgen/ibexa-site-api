<?php

declare(strict_types=1);

namespace Netgen\Bundle\IbexaSiteApiBundle\Tests\Request\ValueResolver;

use Netgen\Bundle\IbexaSiteApiBundle\Request\ValueResolver\SiteValueResolver;
use Netgen\IbexaSiteApi\API\LoadService;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

abstract class SiteValueResolverTestCase extends TestCase
{
    protected LoadService $loadService;
    protected SiteValueResolver $resolver;

    protected function setUp(): void
    {
        $this->loadService = $this->createMock(LoadService::class);
        $this->resolver = $this->createResolver();
    }

    abstract protected function createResolver(): SiteValueResolver;

    protected function createArgumentMetadata(
        string $type,
        bool $nullable = false,
        bool $hasDefault = false,
        mixed $defaultValue = null,
    ): ArgumentMetadata {
        return new ArgumentMetadata(
            name: 'arg',
            type: $type,
            isVariadic: false,
            hasDefaultValue: $hasDefault,
            defaultValue: $defaultValue,
            isNullable: $nullable,
        );
    }
}
