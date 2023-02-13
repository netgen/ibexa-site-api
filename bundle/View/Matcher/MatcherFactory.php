<?php

declare(strict_types=1);

namespace Netgen\Bundle\IbexaSiteApiBundle\View\Matcher;

use Ibexa\Bundle\Core\Matcher\ViewMatcherRegistry;
use Ibexa\Contracts\Core\Repository\Repository;
use Ibexa\Contracts\Core\SiteAccess\ConfigResolverInterface;
use Ibexa\Core\MVC\Symfony\Matcher\ClassNameMatcherFactory;
use Ibexa\Core\MVC\Symfony\Matcher\ViewMatcherInterface;
use Ibexa\Core\MVC\Symfony\View\View;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

use function mb_strpos;
use function mb_substr;

class MatcherFactory extends ClassNameMatcherFactory
{
    use ContainerAwareTrait;

    private ?ViewMatcherRegistry $viewMatcherRegistry;
    private ConfigResolverInterface $configResolver;
    private string $parameterName;
    private ?string $namespace;
    private ?string $scope;

    public function __construct(
        ?ViewMatcherRegistry $viewMatcherRegistry,
        Repository $repository,
        string $relativeNamespace,
        ConfigResolverInterface $configResolver,
        string $parameterName,
        ?string $namespace = null,
        ?string $scope = null
    ) {
        $this->viewMatcherRegistry = $viewMatcherRegistry;
        $this->configResolver = $configResolver;
        $this->parameterName = $parameterName;
        $this->namespace = $namespace;
        $this->scope = $scope;

        parent::__construct($repository, $relativeNamespace);
    }

    public function match(View $view): ?array
    {
        $matchConfig = $this->configResolver->getParameter($this->parameterName, $this->namespace, $this->scope);
        $this->setMatchConfig($matchConfig);

        return parent::match($view);
    }

    /**
     * @param string $matcherIdentifier
     *
     * @return \Ibexa\Core\MVC\Symfony\Matcher\ViewMatcherInterface
     *
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException
     */
    protected function getMatcher($matcherIdentifier): ViewMatcherInterface
    {
        if ($this->viewMatcherRegistry !== null && mb_strpos($matcherIdentifier, '@') === 0) {
            return $this->viewMatcherRegistry->getMatcher(mb_substr($matcherIdentifier, 1));
        }

        if ($this->container->has($matcherIdentifier)) {
            /** @var \Ibexa\Core\MVC\Symfony\Matcher\ViewMatcherInterface $matcher */
            $matcher = $this->container->get($matcherIdentifier);

            return $matcher;
        }

        return parent::getMatcher($matcherIdentifier);
    }
}
