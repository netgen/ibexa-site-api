<?php

declare(strict_types=1);

namespace Netgen\Bundle\IbexaSiteApiBundle\Tests\Request\ParamConverter;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

abstract class AbstractParamConverterTest extends TestCase
{
    protected MockObject $loadServiceMock;

    public function createConfiguration(?string $class = null, ?string $name = null): MockObject|ParamConverter
    {
        $config = $this
            ->getMockBuilder(ParamConverter::class)
            ->onlyMethods(['getClass', 'getAliasName', 'getOptions', 'getName', 'allowArray', 'isOptional'])
            ->disableOriginalConstructor()
            ->getMock();

        if ($name !== null) {
            $config
                ->method('getName')
                ->willReturn($name);
        }

        if ($class !== null) {
            $config
                ->method('getClass')
                ->willReturn($class);
        }

        return $config;
    }
}
