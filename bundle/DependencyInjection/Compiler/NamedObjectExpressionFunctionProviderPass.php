<?php

declare(strict_types=1);

namespace Netgen\Bundle\IbexaSiteApiBundle\DependencyInjection\Compiler;

final class NamedObjectExpressionFunctionProviderPass extends BasedTaggedRegistryPass
{
    protected const RegistryId = 'netgen.ibexa_site_api.named_object.expression_language';
    protected const RegistreeTag = 'netgen.ibexa_site_api.named_object.expression_function_provider';
    protected const RegisterMethod = 'registerProvider';
}
