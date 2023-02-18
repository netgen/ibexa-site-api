<?php

declare(strict_types=1);

namespace Netgen\IbexaSiteApi\Tests\Integration;

use Netgen\IbexaSiteApi\API\Exceptions\TranslationNotMatchedException;

/**
 * Test case for the LoadService.
 *
 * @see \Netgen\IbexaSiteApi\API\LoadService
 *
 * @group integration
 * @group load
 *
 * @depends Netgen\IbexaSiteApi\Tests\Integration\PrepareFixturesTest::testPrepareTestFixtures
 * @depends Netgen\IbexaSiteApi\Tests\Integration\SiteTest::testGetLoadService
 *
 * @internal
 */
final class LoadServiceTest extends BaseTest
{
    /**
     * Test for the loadContent() method.
     *
     * @see \Netgen\IbexaSiteApi\API\LoadService::loadContent()
     */
    public function testLoadContentMatchPrimaryLanguage(): void
    {
        $this->overrideSettings(
            'prioritizedLanguages',
            [
                'eng-GB',
                'ger-DE',
            ],
        );

        $loadService = $this->getSite()->getLoadService();

        $data = $this->getData('eng-GB');
        $content = $loadService->loadContent($data['contentId']);

        $this->assertContent($content, $data);
    }

    /**
     * Test for the loadContent() method.
     *
     * @see \Netgen\IbexaSiteApi\API\LoadService::loadContent()
     */
    public function testLoadContentMatchSecondaryLanguage(): void
    {
        $this->overrideSettings(
            'prioritizedLanguages',
            [
                'eng-US',
                'ger-DE',
            ],
        );

        $loadService = $this->getSite()->getLoadService();

        $data = $this->getData('ger-DE');
        $content = $loadService->loadContent($data['contentId']);

        $this->assertContent($content, $data);
    }

    /**
     * Test for the loadContent() method.
     *
     * @see \Netgen\IbexaSiteApi\API\LoadService::loadContent()
     */
    public function testLoadContentMatchAlwaysAvailableLanguage(): void
    {
        $this->overrideSettings(
            'prioritizedLanguages',
            [
                'eng-US',
            ],
        );

        $loadService = $this->getSite()->getLoadService();

        $data = $this->getData('eng-GB');
        $content = $loadService->loadContent($data['contentId']);

        $this->assertContent($content, $data);
    }

    /**
     * Test for the loadContent() method.
     *
     * @see \Netgen\IbexaSiteApi\API\LoadService::loadContent()
     */
    public function testLoadContentForPreview(): void
    {
        $this->overrideSettings(
            'prioritizedLanguages',
            [
                'eng-US',
            ],
        );

        $loadService = $this->getSite()->getLoadService();

        $data = $this->getData('ger-DE');
        $content = $loadService->loadContentForPreview($data['contentId'], 1, 'ger-DE');

        $this->assertContent($content, $data);
    }

    /**
     * Test for the loadContent() method.
     *
     * @see \Netgen\IbexaSiteApi\API\LoadService::loadContent()
     */
    public function testLoadContentThrowsTranslationNotMatchedException(): void
    {
        $this->overrideSettings(
            'prioritizedLanguages',
            [
                'eng-GB',
                'ger-DE',
            ],
        );

        $this->expectException(TranslationNotMatchedException::class);

        $loadService = $this->getSite()->getLoadService();

        $loadService->loadContent(52);
    }

    /**
     * Test for the loadContent() method.
     *
     * @see \Netgen\IbexaSiteApi\API\LoadService::loadContent()
     */
    public function testLoadContentForPreviewThrowsTranslationNotMatchedException(): void
    {
        $this->overrideSettings(
            'prioritizedLanguages',
            [
                'eng-GB',
                'ger-DE',
            ],
        );

        $this->expectException(TranslationNotMatchedException::class);

        $data = $this->getData('klingon');
        $loadService = $this->getSite()->getLoadService();

        $loadService->loadContentForPreview($data['contentId'], 1, 'klingon');
    }

    /**
     * Test for the loadContentByRemoteId() method.
     *
     * @see \Netgen\IbexaSiteApi\API\LoadService::loadContent()
     */
    public function testLoadContentByRemoteIdMatchPrimaryLanguage(): void
    {
        $this->overrideSettings(
            'prioritizedLanguages',
            [
                'eng-GB',
                'ger-DE',
            ],
        );

        $loadService = $this->getSite()->getLoadService();

        $data = $this->getData('eng-GB');
        $content = $loadService->loadContentByRemoteId($data['contentRemoteId']);

        $this->assertContent($content, $data);
    }

    /**
     * Test for the loadContentByRemoteId() method.
     *
     * @see \Netgen\IbexaSiteApi\API\LoadService::loadContent()
     */
    public function testLoadContentByRemoteIdMatchSecondaryLanguage(): void
    {
        $this->overrideSettings(
            'prioritizedLanguages',
            [
                'eng-US',
                'ger-DE',
            ],
        );

        $loadService = $this->getSite()->getLoadService();

        $data = $this->getData('ger-DE');
        $content = $loadService->loadContentByRemoteId($data['contentRemoteId']);

        $this->assertContent($content, $data);
    }

    /**
     * Test for the loadContentByRemoteId() method.
     *
     * @see \Netgen\IbexaSiteApi\API\LoadService::loadContent()
     */
    public function testLoadContentByRemoteIdMatchAlwaysAvailableLanguage(): void
    {
        $this->overrideSettings(
            'prioritizedLanguages',
            [
                'eng-US',
            ],
        );

        $loadService = $this->getSite()->getLoadService();

        $data = $this->getData('eng-GB');
        $content = $loadService->loadContentByRemoteId($data['contentRemoteId']);

        $this->assertContent($content, $data);
    }

    /**
     * Test for the loadContentByRemoteId() method.
     *
     * @see \Netgen\IbexaSiteApi\API\LoadService::loadContent()
     */
    public function testLoadContentByRemoteIdThrowsTranslationNotMatchedException(): void
    {
        $this->overrideSettings(
            'prioritizedLanguages',
            [
                'eng-GB',
                'ger-DE',
            ],
        );

        $this->expectException(TranslationNotMatchedException::class);

        $loadService = $this->getSite()->getLoadService();

        $loadService->loadContentByRemoteId('27437f3547db19cf81a33c92578b2c89');
    }

    /**
     * Test for the loadLocation() method.
     *
     * @see \Netgen\IbexaSiteApi\API\LoadService::loadContent()
     */
    public function testLoadLocationMatchPrimaryLanguage(): void
    {
        $this->overrideSettings(
            'prioritizedLanguages',
            [
                'eng-GB',
                'ger-DE',
            ],
        );

        $loadService = $this->getSite()->getLoadService();

        $data = $this->getData('eng-GB');
        $location = $loadService->loadLocation($data['locationId']);

        $this->assertLocation($location, $data);
    }

    /**
     * Test for the loadLocation() method.
     *
     * @see \Netgen\IbexaSiteApi\API\LoadService::loadContent()
     */
    public function testLoadLocationMatchSecondaryLanguage(): void
    {
        $this->overrideSettings(
            'prioritizedLanguages',
            [
                'eng-US',
                'ger-DE',
            ],
        );

        $loadService = $this->getSite()->getLoadService();

        $data = $this->getData('ger-DE');
        $location = $loadService->loadLocation($data['locationId']);

        $this->assertLocation($location, $data);
    }

    /**
     * Test for the loadLocation() method.
     *
     * @see \Netgen\IbexaSiteApi\API\LoadService::loadContent()
     */
    public function testLoadLocationMatchAlwaysAvailableLanguage(): void
    {
        $this->overrideSettings(
            'prioritizedLanguages',
            [
                'eng-US',
            ],
        );

        $loadService = $this->getSite()->getLoadService();

        $data = $this->getData('eng-GB');
        $location = $loadService->loadLocation($data['locationId']);

        $this->assertLocation($location, $data);
    }

    /**
     * Test for the loadLocation() method.
     *
     * @see \Netgen\IbexaSiteApi\API\LoadService::loadContent()
     */
    public function testLoadLocationThrowsTranslationNotMatchedException(): void
    {
        $this->overrideSettings(
            'prioritizedLanguages',
            [
                'eng-GB',
                'ger-DE',
            ],
        );

        $this->expectException(TranslationNotMatchedException::class);

        $loadService = $this->getSite()->getLoadService();

        $loadService->loadLocation(54);
    }

    /**
     * Test for the loadLocationByRemoteId() method.
     *
     * @see \Netgen\IbexaSiteApi\API\LoadService::loadContent()
     */
    public function testLoadLocationByRemoteIdMatchPrimaryLanguage(): void
    {
        $this->overrideSettings(
            'prioritizedLanguages',
            [
                'eng-GB',
                'ger-DE',
            ],
        );

        $loadService = $this->getSite()->getLoadService();

        $data = $this->getData('eng-GB');
        $location = $loadService->loadLocationByRemoteId($data['locationRemoteId']);

        $this->assertLocation($location, $data);
    }

    /**
     * Test for the loadLocationByRemoteId() method.
     *
     * @see \Netgen\IbexaSiteApi\API\LoadService::loadContent()
     */
    public function testLoadLocationByRemoteIdMatchSecondaryLanguage(): void
    {
        $this->overrideSettings(
            'prioritizedLanguages',
            [
                'eng-US',
                'ger-DE',
            ],
        );

        $loadService = $this->getSite()->getLoadService();

        $data = $this->getData('ger-DE');
        $location = $loadService->loadLocationByRemoteId($data['locationRemoteId']);

        $this->assertLocation($location, $data);
    }

    /**
     * Test for the loadLocationByRemoteId() method.
     *
     * @see \Netgen\IbexaSiteApi\API\LoadService::loadContent()
     */
    public function testLoadLocationByRemoteIdMatchAlwaysAvailableLanguage(): void
    {
        $this->overrideSettings(
            'prioritizedLanguages',
            [
                'eng-US',
            ],
        );

        $loadService = $this->getSite()->getLoadService();

        $data = $this->getData('eng-GB');
        $location = $loadService->loadLocationByRemoteId($data['locationRemoteId']);

        $this->assertLocation($location, $data);
    }

    /**
     * Test for the loadLocationByRemoteId() method.
     *
     * @see \Netgen\IbexaSiteApi\API\LoadService::loadContent()
     */
    public function testLoadLocationByRemoteIdThrowsTranslationNotMatchedException(): void
    {
        $this->overrideSettings(
            'prioritizedLanguages',
            [
                'eng-GB',
                'ger-DE',
            ],
        );

        $this->expectException(TranslationNotMatchedException::class);

        $loadService = $this->getSite()->getLoadService();

        $loadService->loadLocationByRemoteId('fa9f3cff9cf90ecfae335718dcbddfe2');
    }
}
