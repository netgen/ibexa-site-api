<?php

declare(strict_types=1);

namespace Netgen\Bundle\IbexaSiteApiBundle\DependencyInjection\Compiler;

use Netgen\IbexaSiteApi\API\Values\DebugInfo;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\VarDumper\Caster\Caster;

class VarDumperPass implements CompilerPassInterface
{
    private const ClonerId = 'var_dumper.cloner';

    public function process(ContainerBuilder $container): void
    {
        if (!$container->hasDefinition(self::ClonerId)) {
            return;
        }

        $clonerDefinition = $container->findDefinition(self::ClonerId);

        $clonerDefinition->addMethodCall(
            'addCasters',
            [
                '$casters' => [
                    DebugInfo::class => [self::class, 'cast'],
                ],
            ],
        );
    }

    public static function cast(DebugInfo $object, array $array): array
    {
        $debugInfo = $object->getDebugInfo();
        $debugData = [];

        foreach ($debugInfo as $key => $value) {
            if (!isset($key[0]) || $key[0] !== "\0") {
                if (array_key_exists(Caster::PREFIX_DYNAMIC . $key, $array)) {
                    continue;
                }

                $key = Caster::PREFIX_VIRTUAL . $key;
            }

            unset($array[$key]);

            $debugData[$key] = $value;
        }

        return array_merge($debugData, $array);
    }
}
