<?php

declare(strict_types=1);

namespace Netgen\Bundle\IbexaSiteApiBundle\DependencyInjection\Compiler;

final class RedirectExpressionFunctionProviderPass extends BaseTaggedRegistryPass
{
    protected const RegistryId = 'netgen.ibexa_site_api.redirect.expression_language';
    protected const RegistreeTag = 'netgen.ibexa_site_api.redirect.expression_function_provider';
    protected const RegisterMethod = 'registerProvider';
}
