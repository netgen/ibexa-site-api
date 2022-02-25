<?php

declare(strict_types=1);

namespace Netgen\IbexaSiteApi\Core\Traits;

use Netgen\IbexaSiteApi\API\Site;

trait SiteAwareTrait
{
    protected ?Site $site;

    /**
     * Site setter.
     */
    public function setSite(Site $site): void
    {
        $this->site = $site;
    }

    /**
     * Site getter.
     */
    protected function getSite(): Site
    {
        return $this->site;
    }
}
