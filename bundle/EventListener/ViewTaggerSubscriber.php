<?php

declare(strict_types=1);

namespace Netgen\Bundle\IbexaSiteApiBundle\EventListener;

use Ibexa\Contracts\HttpCache\ResponseTagger\ResponseTagger;
use Netgen\Bundle\IbexaSiteApiBundle\Event\RenderViewEvent;
use Netgen\Bundle\IbexaSiteApiBundle\Events;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final readonly class ViewTaggerSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private ResponseTagger $responseTagger,
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [
            Events::RENDER_VIEW => 'tagView',
        ];
    }

    public function tagView(RenderViewEvent $event): void
    {
        $this->responseTagger->tag($event->getView());
    }
}
