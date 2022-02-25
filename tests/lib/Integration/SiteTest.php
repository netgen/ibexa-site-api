<?php

declare(strict_types=1);

namespace Netgen\IbexaSiteApi\Tests\Integration;

/**
 * Test case for the Site.
 *
 * @see \Netgen\IbexaSiteApi\API\Site
 *
 * @group site
 *
 * @internal
 */
final class SiteTest extends BaseTest
{
    /**
     * Test for the getSettings() method.
     *
     * @see \Netgen\IbexaSiteApi\API\Site::getSettings()
     *
     * @throws \ErrorException
     */
    public function testGetSettings(): void
    {
        $this->getSite()->getSettings();

        $this->addToAssertionCount(1);
    }

    /**
     * Test for the getFilterService() method.
     *
     * @group filter
     *
     * @see \Netgen\IbexaSiteApi\API\Site::getFilterService()
     *
     * @throws \ErrorException
     */
    public function testGetFilterService(): void
    {
        $this->getSite()->getFilterService();

        $this->addToAssertionCount(1);
    }

    /**
     * Test for the getFindService() method.
     *
     * @group find
     *
     * @see \Netgen\IbexaSiteApi\API\Site::getFindService()
     *
     * @throws \ErrorException
     */
    public function testGetFindService(): void
    {
        $this->getSite()->getFindService();

        $this->addToAssertionCount(1);
    }

    /**
     * Test for the getLoadService() method.
     *
     * @group load
     *
     * @see \Netgen\IbexaSiteApi\API\Site::getLoadService()
     *
     * @throws \ErrorException
     */
    public function testGetLoadService(): void
    {
        $this->getSite()->getLoadService();

        $this->addToAssertionCount(1);
    }
}
