<?php

declare(strict_types=1);

namespace Netgen\Bundle\IbexaSiteApiBundle\EventListener;

use Ibexa\Contracts\Core\SiteAccess\ConfigResolverInterface;
use Ibexa\Core\MVC\Symfony\Routing\UrlAliasRouter;
use Netgen\Bundle\IbexaSiteApiBundle\Exception\InvalidRedirectConfiguration;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class InvalidRedirectConfigurationListener implements EventSubscriberInterface
{
    private LoggerInterface $logger;
    private UrlGeneratorInterface $urlGenerator;
    private ConfigResolverInterface $configResolver;

    public function __construct(
        UrlGeneratorInterface $urlGenerator,
        ConfigResolverInterface $configResolver,
        ?LoggerInterface $logger = null
    ) {
        $this->urlGenerator = $urlGenerator;
        $this->configResolver = $configResolver;
        $this->logger = $logger ?? new NullLogger();
    }

    public static function getSubscribedEvents(): array
    {
        return [KernelEvents::EXCEPTION => 'onKernelException'];
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();

        if (!$exception instanceof InvalidRedirectConfiguration) {
            return;
        }

        $this->logger->critical($exception->getMessage());

        $rootLocationId = $this->configResolver->getParameter('content.tree_root.location_id');
        $event->setResponse(
            new RedirectResponse(
                $this->urlGenerator->generate(UrlAliasRouter::URL_ALIAS_ROUTE_NAME, ['locationId' => $rootLocationId]),
                Response::HTTP_FOUND
            )
        );
    }
}
