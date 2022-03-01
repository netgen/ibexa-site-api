<?php

declare(strict_types=1);

namespace Netgen\IbexaSiteApi\Core\Site;

use Ibexa\Contracts\Core\Repository\Exceptions\PropertyNotFoundException;
use Ibexa\Contracts\Core\Repository\Exceptions\PropertyReadOnlyException;
use Netgen\IbexaSiteApi\API\Settings as BaseSettings;

/**
 * @internal
 *
 * Hint against API abstract class instead of this service:
 *
 * @see \Netgen\IbexaSiteApi\API\Settings
 */
final class Settings extends BaseSettings
{
    /**
     * @var string[]
     */
    private $prioritizedLanguages;

    /**
     * @var bool
     */
    private $useAlwaysAvailable;

    /**
     * @var int
     */
    private $rootLocationId;

    /**
     * @var bool
     */
    private $showHiddenItems;

    /**
     * @var bool
     */
    private $failOnMissingField;

    /**
     * @param string[] $prioritizedLanguages
     */
    public function __construct(
        array $prioritizedLanguages,
        bool $useAlwaysAvailable,
        int $rootLocationId,
        bool $showHiddenItems,
        bool $failOnMissingField
    ) {
        $this->prioritizedLanguages = $prioritizedLanguages;
        $this->useAlwaysAvailable = $useAlwaysAvailable;
        $this->rootLocationId = $rootLocationId;
        $this->showHiddenItems = $showHiddenItems;
        $this->failOnMissingField = $failOnMissingField;
    }

    /**
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\PropertyNotFoundException
     *
     * @return bool|int|string[]
     */
    public function __get(string $property)
    {
        switch ($property) {
            case 'prioritizedLanguages':
                return $this->prioritizedLanguages;

            case 'useAlwaysAvailable':
                return $this->useAlwaysAvailable;

            case 'rootLocationId':
                return $this->rootLocationId;

            case 'showHiddenItems':
                return $this->showHiddenItems;

            case 'failOnMissingField':
                return $this->failOnMissingField;
        }

        throw new PropertyNotFoundException($property, __CLASS__);
    }

    /**
     * @param mixed $value
     *
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\PropertyReadOnlyException
     */
    public function __set(string $property, $value): void
    {
        throw new PropertyReadOnlyException($property, __CLASS__);
    }

    /**
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\PropertyNotFoundException
     */
    public function __isset(string $property): bool
    {
        switch ($property) {
            case 'prioritizedLanguages':
            case 'useAlwaysAvailable':
            case 'rootLocationId':
            case 'showHiddenItems':
            case 'failOnMissingField':
                return true;
        }

        throw new PropertyNotFoundException($property, __CLASS__);
    }
}
