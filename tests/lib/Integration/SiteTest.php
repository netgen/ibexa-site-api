<?php

declare(strict_types=1);

namespace Netgen\IbexaSiteApi\Tests\Integration;

use PHPUnit\Framework\Attributes\Group;

/**
 * Test case for the Site.
 *
 * @see \Netgen\IbexaSiteApi\API\Site
 *
 * @internal
 */
#[Group('site')]
final class SiteTest extends BaseTest
{
    /**
     * Test for the getSettings() method.
     *
     * @see \Netgen\IbexaSiteApi\API\Site::getSettings()
     */
    public function testGetSettings(): void
    {
        $this->getSite()->getSettings();

        $this->addToAssertionCount(1);
    }

    /**
     * Test for the getFilterService() method.
     *
     * @see \Netgen\IbexaSiteApi\API\Site::getFilterService()
     */
    #[Group('filter')]
    public function testGetFilterService(): void
    {
        $this->getSite()->getFilterService();

        $this->addToAssertionCount(1);
    }

    /**
     * Test for the getFindService() method.
     *
     * @see \Netgen\IbexaSiteApi\API\Site::getFindService()
     */
    #[Group('find')]
    public function testGetFindService(): void
    {
        $this->getSite()->getFindService();

        $this->addToAssertionCount(1);
    }

    /**
     * Test for the getLoadService() method.
     *
     * @see \Netgen\IbexaSiteApi\API\Site::getLoadService()
     */
    #[Group('load')]
    public function testGetLoadService(): void
    {
        $this->getSite()->getLoadService();

        $this->addToAssertionCount(1);
    }
}
