<?php

declare(strict_types=1);

namespace Netgen\IbexaSiteApi\API;

/**
 * Site interface.
 */
interface Site
{
    /**
     * Settings getter.
     *
     * @return \Netgen\IbexaSiteApi\API\Settings
     */
    public function getSettings(): Settings;

    /**
     * FilterService getter.
     *
     * @return \Netgen\IbexaSiteApi\API\FilterService
     */
    public function getFilterService(): FilterService;

    /**
     * FindService getter.
     *
     * @return \Netgen\IbexaSiteApi\API\FindService
     */
    public function getFindService(): FindService;

    /**
     * LoadService getter.
     *
     * @return \Netgen\IbexaSiteApi\API\LoadService
     */
    public function getLoadService(): LoadService;

    /**
     * RelationService getter.
     *
     * @return \Netgen\IbexaSiteApi\API\RelationService
     */
    public function getRelationService(): RelationService;
}
