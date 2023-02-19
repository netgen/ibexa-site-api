<?php

declare(strict_types=1);

namespace Netgen\Bundle\IbexaSiteApiBundle\Event;

use Ibexa\Core\MVC\Symfony\View\View;
use Symfony\Contracts\EventDispatcher\Event;

class RenderViewEvent extends Event
{
    public function __construct(
        private readonly View $view,
    ) {
    }

    public function getView(): View
    {
        return $this->view;
    }
}
