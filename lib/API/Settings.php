<?php

declare(strict_types=1);

namespace Netgen\IbexaSiteApi\API;

/**
 * Site Settings.
 *
 * @property array $prioritizedLanguages Array of prioritized languages
 * @property bool $useAlwaysAvailable Always available fallback state
 * @property int $rootLocationId Root Location ID
 * @property bool $showHiddenItems Whether to show hidden Locations and Content items
 * @property bool $failOnMissingField Whether to fail on a missing Content Field
 */
abstract class Settings {}
