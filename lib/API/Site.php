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
     */
    public function getSettings(): Settings;

    /**
     * FilterService getter.
     */
    public function getFilterService(): FilterService;

    /**
     * FindService getter.
     */
    public function getFindService(): FindService;

    /**
     * LoadService getter.
     */
    public function getLoadService(): LoadService;

    /**
     * RelationService getter.
     */
    public function getRelationService(): RelationService;
}
