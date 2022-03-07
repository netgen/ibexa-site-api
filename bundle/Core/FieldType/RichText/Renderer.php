<?php

declare(strict_types=1);

namespace Netgen\Bundle\IbexaSiteApiBundle\Core\FieldType\RichText;

use Ibexa\Contracts\Core\Repository\Repository;
use Ibexa\Contracts\Core\SiteAccess\ConfigResolverInterface;
use Ibexa\FieldTypeRichText\RichText\Renderer as CoreRenderer;
use Psr\Log\LoggerInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Twig\Environment;

class Renderer extends CoreRenderer
{
    private string $ngEmbedConfigurationNamespace;

    public function __construct(
        Repository $repository,
        AuthorizationCheckerInterface $authorizationChecker,
        ConfigResolverInterface $configResolver,
        Environment $twig,
        string $tagConfigurationNamespace,
        string $styleConfigurationNamespace,
        string $embedConfigurationNamespace,
        string $ngEmbedConfigurationNamespace,
        ?LoggerInterface $logger = null,
        array $customTagsConfiguration = [],
        array $customStylesConfiguration = []
    ) {
        parent::__construct(
            $repository,
            $authorizationChecker,
            $configResolver,
            $twig,
            $tagConfigurationNamespace,
            $styleConfigurationNamespace,
            $embedConfigurationNamespace,
            $logger,
            $customTagsConfiguration,
            $customStylesConfiguration,
        );

        $this->ngEmbedConfigurationNamespace = $ngEmbedConfigurationNamespace;
    }

    protected function getEmbedTemplateName($resourceType, $isInline, $isDenied): ?string
    {
        $configurationReference = $this->getConfigurationReference();

        if ($resourceType === static::RESOURCE_TYPE_CONTENT) {
            $configurationReference .= '.content';
        } else {
            $configurationReference .= '.location';
        }

        if ($isInline) {
            $configurationReference .= '_inline';
        }

        if ($isDenied) {
            $configurationReference .= '_denied';
        }

        if ($this->configResolver->hasParameter($configurationReference)) {
            $configuration = $this->configResolver->getParameter($configurationReference);

            return $configuration['template'];
        }

        $this->logger->warning(
            "Embed tag configuration '{$configurationReference}' was not found",
        );

        $configurationReference = $this->getConfigurationReference();

        $configurationReference .= '.default';

        if ($isInline) {
            $configurationReference .= '_inline';
        }

        if ($this->configResolver->hasParameter($configurationReference)) {
            $configuration = $this->configResolver->getParameter($configurationReference);

            return $configuration['template'];
        }

        $this->logger->warning(
            "Embed tag default configuration '{$configurationReference}' was not found",
        );

        return null;
    }

    private function getConfigurationReference(): string
    {
        $isSiteApiPrimaryContentView = $this->configResolver->getParameter('ng_site_api.site_api_is_primary_content_view');

        /** @var bool $isSiteApiPrimaryContentView */
        if ($isSiteApiPrimaryContentView) {
            return $this->ngEmbedConfigurationNamespace;
        }

        return $this->embedConfigurationNamespace;
    }
}
